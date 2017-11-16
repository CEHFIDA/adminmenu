<?php

namespace Selfreliance\adminmenu;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;

class AdminMenuController extends Controller
{
    private $dirResult = array();

    /**
     * Scan directory with recursive method
    */
    public function scandir_recursive($dir) {
        $dir = rtrim($dir, DIRECTORY_SEPARATOR);
        $result = array();
        foreach (scandir($dir) as $node) {
            if ($node !== '.' and $node !== '..') {
                if($node == "description.json") {
                    array_push($this->dirResult, $dir."/".$node);
                }
                if (is_dir($dir . DIRECTORY_SEPARATOR . $node)) {
                    $this->scandir_recursive($dir . DIRECTORY_SEPARATOR . $node, $result);
                } else {
                    $result[$node][] = $dir . DIRECTORY_SEPARATOR . $node;
                }
            }
        }
    }

    /**
     * Get packages
     * @param string $dir
     * @return result scan
    */
    public function getPackages($dir)
    {
        $this->scandir_recursive($dir);
        $decodeArrayJson = array();
        foreach ($this->dirResult as $result) {
            array_push($decodeArrayJson, json_decode(\File::get($result)));
        }
        $this->dirResult = array();
        
        return $decodeArrayJson;
    }
 
    /**
     * Index
     * @return view home with tree, new_package, dev_packages
    */   
    public function index()
    {
        $menu = DB::table('admin__menu')->orderBy('sort', 'asc')->get();
        $new_packages = $this->getPackages(realpath(__DIR__ . '/../..'));

        $current_packages = array();
        $menu->each(function($row) use (&$current_packages){
            $current_packages[] = $row->package;
        });

        $new = array();
        foreach($new_packages as $p1)
        {
            if(!in_array($p1->package, $current_packages)) $new[] = $p1;
        }

        $dev = array();
        if(\File::isDirectory(base_path("packages")))
        {
            $dev_packages = $this->getPackages(base_path("packages"));
            foreach($dev_packages as $p2)
            {
                if(!in_array($p2->package, $current_packages)) 
                {
                    $p2->name .= ' [dev]';
                    $new[] = $p2;
                }
            }
        }

        $result = makeMenu($menu, null, 2);

        return view('adminmenu::home')->with([
            'tree' => $result, 
            'new_packages' => $new,
            'dev_packages' => $dev
        ]);
    }

    /**
     * Create item
     * @param request $request
     * @param string $type
     * @return mixed
    */
    public function create_item(Request $request, $type)
    {
        if($type == 'package')
        {
            if(!is_null($request['selected_package']))
            {
                foreach($request['selected_package'] as $selected)
                {
                    $info = explode(':', $selected);

                    DB::table('admin__menu')->insert([
                        'title' => $info[1],
                        'package' => $info[0],
                        'icon' => $info[2],
                        'parent' => 0,
                        'sort' => 0
                    ]);
                }

                flash()->success( trans('translate-menu::menu.sectionCreated') );
            }
        }
        else if($type == 'stub')
        {
            $this->validate($request, [
                'title' => 'required|min:2'
            ]);

            DB::table('admin__menu')->insert([
                'title' => $request['title'],
                'package' => 'nope',
                'icon' => '',
                'parent' => 0,
                'sort' => 0
            ]);

            flash()->success( trans('translate-menu::menu.sectionCreated') );
        }
        return redirect()->route('AdminMenuHome');
    }

    /**
     * Update
     * @param request $request
     * @param string $type
     * @return mixed
    */
    public function update(Request $request, $type)
    {
        if($type == 'tree') return $this->update_tree(json_decode(json_encode($request['tree'])));
        else if($type == 'category')
        {
            $this->validate($request, [
                'id' => 'required',
                'title' => 'required|min:2'
            ]);

            DB::table('admin__menu')->where(
                'id', $request->input('id')
            )->update(
                [
                    'title' => str_replace('&nbsp;', ' ', htmlentities($request->input('title'), null, 'utf-8')),
                    'icon' => ($request->input('icon')) ? str_replace('&nbsp;', ' ', htmlentities($request->input('icon'), null, 'utf-8')) : ''
                ]
            );

            flash()->success( trans('translate-menu::menu.sectionUpdated') );
        }
        return redirect()->route('AdminMenuHome');
    }

    /**
     * Destroy category
     * @param int $id
     * @return mixed
    */
    public function destroy($id)
    {
        DB::table('admin__menu')->where('id', $id)->delete();

        $childs = DB::table('admin__menu')->where('parent', $id)->get();
        if(count($childs) > 0) $childs->delete();

        flash()->success( trans('translate-menu::menu.sectionDeleted') );

        return redirect()->route('AdminMenuHome');
    }

    /**
     * Update tree
     * @param json $menu
     * @param int $parent
     * @return json response
    */
    public function update_tree($menu, $parent = 0)
    {
        $i = 1;
        foreach ($menu as $item)
        {
            if(isset($item->children))
            {
                DB::table('admin__menu')->where(
                    'id', $item->id
                )->update(
                    ['parent' => $parent,'sort' => $i]
                );

                $this->update_tree($item->children,$item->id);
            }
            else if(isset($item->id)){
                DB::table('admin__menu')->where(
                    'id', $item->id
                )->update(
                    ['parent' => $parent,'sort' => $i]
                );
            }
            $i++;
        }

        return \Response::json([
            "success" => true,
            "msg" => "Succesfuly update!"
        ], "200");
    }
}