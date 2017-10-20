<?php

    function makeMenu($menu, $pages, $type)
    {
        $new = array();
        foreach ($menu as $a){
            $new[$a->parent][] = $a;
        }
        $tree = createTree($new, $new[0], $pages);
        return showTree($tree, $type);
    }

    function createTree(&$list, $parent, &$accessible)
    {
        $tree = array();
        foreach ($parent as $k=>$l){
            if(is_null($accessible)){
                if(isset($list[$l->id])){
                    $l->children = createTree($list, $list[$l->id], $accessible);
                }
                $tree[] = $l;
            }else if(in_array($l->package, $accessible) || $l->package == 'nope'){
                if(isset($list[$l->id])){
                    $l->children = createTree($list, $list[$l->id], $accessible);
                }
                $tree[] = $l;
            }
        }
        return $tree;
    }

    function getTree($category, $type)
    {
        if($type == 1){
            $package = ($category->package == config('adminamazing.path')) ? config('adminamazing.path') : config('adminamazing.path').'/'.$category->package;
            $check = (\Request::route()->getPrefix() == $package) ? ' active' : NULL;
            $menu = '<li>';
            $icon = ($category->parent == 0) ? '<i class="'.$category->icon.'"></i>' : '';
            $menu .= '<a class="has-arrow'.$check.'" href="'.url($package).'" aria-expanded="false">'.$icon.$category->title.'</a>';
            if(isset($category->children)){
                $menu .= '<ul aria-expanded="false" class="collapse">'.showTree($category->children, $type).'</ul>';
            }
            $menu .= '</li>';
        }else if($type == 2){
            $info = json_encode(['id' => $category->id, 'title' => str_replace(' ', '&nbsp;', $category->title), 'icon' => str_replace(' ', '&nbsp;', $category->icon)]);
            $menu = '<li class="dd-item dd3-item" data-id="'.$category->id.'">';
            $menu .= '<div class="dd-handle dd3-handle"></div>';
            $menu .= '<div class="dd3-content">'.$category->title.' <a href="#editModal" class="edit_toggle" data-rel='.$info.' data-toggle="modal"><i class="fa fa-pencil text-inverse m-r-10"></i></a>';
            $menu .= '<div class="pull-right"><a href="#deleteModal" class="delete_toggle" data-rel='.$category->id.' data-toggle="modal"><i class="fa fa-close text-danger"></i></a></div></div>';
            if(isset($category->children)){
                $menu .= '<ol class="dd-list">'.showTree($category->children, $type).'</ol>';
            }
        }
        return $menu;
    }

    function showTree($data, $type)
    {
        $string = '';
        foreach($data as $item){
            if(!empty($item->package)){
                $string .= getTree($item, $type);
            }
        }
        return $string;
    }