<?php

namespace Selfreliance\adminmenu;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use File;
use Selfreliance\Adminamazing\AdminController;

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

    public function getPackages()
    {
        $this->scandir_recursive(realpath(__DIR__ . '/../..'));
        $decodeArrayJson = array();
        foreach ($this->dirResult as $result) {
            array_push($decodeArrayJson, json_decode(File::get($result)));
        }
        return $decodeArrayJson;
    }
    
    public function index()
    {
        $menu = \DB::table('admin__menu')->orderBy('sort', 'asc')->get();
        $get_packages = $this->getPackages();

        $current_packages = array();
        $menu->each(function($row) use (&$current_packages){
            $current_packages[] = $row->package;
        });

        $new_packages = array();
        foreach($get_packages as $package)
        {
            if(!in_array($package->package, $current_packages)) $new_packages[] = $package;
        }

        $result = AdminController::makeMenu($menu, null, 2);
        return view('adminmenu::home')->with(['tree' => $result, 'new_packages' => $new_packages]);
    }

    public function add(Request $request)
    {
        foreach($request['selected_package'] as $selected)
        {
            $info = explode(':', $selected);
            \DB::table('admin__menu')->insert(
                [
                    'title' => $info[1], 
                    'package' => $info[0], 
                    'parent' => 0, 
                    'sort' => 0
                ]
            );
        }
        return redirect()->route('AdminMenuHome');
    }

    public function delete($id)
    {
        \DB::table('admin__menu')->where('id', $id)->delete();
        \DB::table('admin__menu')->where('parent', $id)->delete();
        return redirect()->route('AdminMenuHome');
    }

    public function update_tree(Request $request)
    {
        $tree = json_decode(json_encode($request['tree']));
        return $this->output_tree($tree);
    }

    public function output_tree($menu,$parent = 0)
    {
        $i = 1;
        foreach ($menu as $item)
        {
            if(isset($item->children))
            {
                \DB::table('admin__menu')->where('id', $item->id)->update(['parent' => $parent,'sort' => $i]);
                $this->output_tree($item->children,$item->id);
            }
            else if(isset($item->id)) \DB::table('admin__menu')->where('id', $item->id)->update(['parent' => $parent,'sort' => $i]);
            $i++;
        }
        return \Response::json(["success" => true,"msg" => "Succesfuly update!"], "200");
    }
}