<?php

namespace Selfreliance\adminmenu;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Selfreliance\Adminmenu\Models\AdminMenu;

class AdminMenuController extends Controller
{
    private $menu;

    public function __construct(AdminMenu $model)
    {
        $this->menu = $model;
    }

    public function index()
    {
        $menu = $this->menu->orderBy('sort', 'asc')->get();
        $new = self::getPackages(realpath(__DIR__ . '/../..'));

        $current_packages = array();
        $menu->each(function($row) use (&$current_packages){
            $current_packages[] = $row->package;
        });

        $new_packages = array();
        foreach($new as $p1)
        {
            if(!in_array($p1->package, $current_packages)) $new_packages[] = $p1;
        }

        $dev_packages = array();
        if(\File::isDirectory(base_path("packages")))
        {
            $dev = $this->getPackages(base_path("packages"));
            foreach($dev as $p2)
            {
                if(!in_array($p2->package, $current_packages)) 
                {
                    $p2->name .= ' [dev]';
                    $dev_packages[] = $p2;
                }
            }
        }

        $tree = \Menu::make($menu, null, 2);

        return view('adminmenu::home', compact('new_packages', 'dev_packages', 'tree'));
    }

    public function addPackages(Request $request)
    {
        if(!is_null($request['selected_packages']))
        {
            foreach($request['selected_packages'] as $selected)
            {
                $info = explode(':', $selected);

                $data = [
                    'title' => $info[1],
                    'package' => $info[0],
                    'icon' => $info[2],
                    'parent' => 0,
                    'sort' => 0
                ];

                $this->menu->create($data);
            }

            flash()->success('Раздел создан');
        }

        return redirect()->route('AdminMenuHome');
    }

    public function createStub(Request $request)
    {
        $this->validate($request, [
            'title' => 'required|min:2'
        ]);

        $data = [
            'title' => $request['title'],
            'package' => 'nope',
            'icon' => '',
            'parent' => 0,
            'sort' => 0
        ];

        $this->menu->create($data);

        flash()->success('Раздел создан');

        return redirect()->route('AdminMenuHome');
    }

    public function updateCategory(Request $request)
    {
        $this->validate($request, [
            'title' => 'required|min:2'
        ]);

        $item = $this->menu->findOrFail($request['id']);

        $data = [
            'title' => $this->menu->normalSpace($request['title']),
            'icon' => ($request['icon']) ? $this->menu->normalSpace($request['icon']) : ''
        ];

        $item->update($data);

        flash()->success('Раздел обновлен');

        return redirect()->route('AdminMenuHome');
    }

    public function updateTree(Request $request)
    {
        return $this->treeSynchronization(json_decode(json_encode($request['tree'])));
    }

    public function destroy($id)
    {
        $item = $this->menu->findOrFail($id);
        $item->delete();

        $childs = AdminMenu::where('parent', $id)->get();
        if(count($childs) > 0)
        {
            $childs->delete();
        }

        flash()->success('Раздел удален');

        return redirect()->route('AdminMenuHome');
    }

    public function treeSynchronization($tree, $parent = 0)
    {
        $i = 1;
        foreach($tree as $item)
        {
            if(isset($item->children))
            {
                $this->menu->where('id', $item->id)->update([
                    'parent' => $parent,
                    'sort' => $i
                ]);

                self::treeSynchronization($item->children,$item->id);
            }
            else if(isset($item->id))
            {
                $this->menu->where('id', $item->id)->update([
                    'parent' => $parent,
                    'sort' => $i
                ]);
            }

            $i++;
        }
    }

    public function getPackages($dir)
    {
        $descriptions = collect([]);
        $files = \File::allFiles($dir);
        foreach($files as $file)
        {
            if($file->getFileName() == 'description.json')
            {
                $descriptions->push(json_decode(\File::get($file)));
            }
        }
        return $descriptions;
    }    
}