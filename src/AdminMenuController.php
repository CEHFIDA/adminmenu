<?php

namespace Selfreliance\adminmenu;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;

class AdminMenuController extends Controller
{
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

    public function index()
    {
        $menu = DB::table('admin__menu')->orderBy('sort', 'asc')->get();
        $new = $this->getPackages(realpath(__DIR__ . '/../..'));

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

                flash()->success('Раздел успешно создан');
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

            flash()->success('Раздел успешно создан');
        }
        return redirect()->route('AdminMenuHome');
    }

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

            flash()->success('Раздел обновлен');
        }
        return redirect()->route('AdminMenuHome');
    }

    public function destroy($id)
    {
        DB::table('admin__menu')->where('id', $id)->delete();

        $childs = DB::table('admin__menu')->where('parent', $id)->get();
        if(count($childs) > 0) $childs->delete();

        flash()->success('Раздел удален');

        return redirect()->route('AdminMenuHome');
    }

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