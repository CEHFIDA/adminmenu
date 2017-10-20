<?php

namespace Selfreliance\adminmenu;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use File;

class AdminMenuController extends Controller
{
    private $dirResult = array();

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

    public function getPackages($dir)
    {
        $this->scandir_recursive($dir);
        $decodeArrayJson = array();
        foreach ($this->dirResult as $result) {
            array_push($decodeArrayJson, json_decode(File::get($result)));
        }
        $this->dirResult = array();
        return $decodeArrayJson;
    }
    
    public function index()
    {
        $menu = DB::table('admin__menu')->orderBy('sort', 'asc')->get();
        $new_packages = $this->getPackages(realpath(__DIR__ . '/../..'));
        $dev_packages = $this->getPackages(base_path("/packages"));

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
        foreach($dev_packages as $p2)
        {
            if(!in_array($p2->package, $current_packages)) 
            {
                $p2->name .= ' [dev]';
                $new[] = $p2;
            }
        }

        $result = makeMenu($menu, null, 2);
        return view('adminmenu::home')->with([
            'tree' => $result, 
            'new_packages' => $new,
            'dev_packages' => $dev
        ]);
    }

    public function action(Request $request, $name = null)
    {
        $method = $request->method();
        if($method == 'POST'){// Create package or stub
            if($name == 'package'){
                foreach($request['selected_package'] as $selected){
                    $info = explode(':', $selected);

                    DB::table('admin__menu')->insert([
                        'title' => $info[1],
                        'package' => $info[0],
                        'icon' => $info[2],
                        'parent' => 0,
                        'sort' => 0
                    ]);
                }
            }else if($name == 'stub'){
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
            }
        }else if($method == 'PUT'){// Update tree or title
            if($name == 'tree'){
                $tree = json_decode(json_encode($request['tree']));
                return $this->update_tree($tree);
            }else if($name == 'title'){
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
            }
        }else if($method == 'DELETE'){// Delete category
            $this->validate($request, [
                'id' => 'required'
            ]);

            DB::table('admin__menu')->where(
                'id', $request->input('id')
            )->delete();

            $childs = DB::table('admin__menu')->where(
                'parent', $request->input('id')
            )->get();

            if(count($childs) > 0) $childs->delete();
        }
        return redirect()->route('AdminMenuHome');
    }

    public function update_tree($menu,$parent = 0)
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
        return \Response::json(["success" => true,"msg" => "Succesfuly update!"], "200");
    }
}