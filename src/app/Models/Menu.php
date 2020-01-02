<?php

namespace Backpack\MenuCRUD\app\Models;

use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\app\Models\Traits\CrudTrait;

class Menu extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */
    protected $table = 'menus';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $fillable = ['name', 'position'];

    public function __constructor()
    {
        $this->registerEnumWithDoctrine();
    }
    public function menu_items()
    {
        return $this->hasMany('Backpack\MenuCRUD\app\Models\MenuItem', 'menu_id');
    }
    public static function getTree($position)
    {
        $menu = Menu::where('position', $position)->first();
        $menu_items = $menu->menu_items()->orderBy('lft')->get();

        if ($menu_items->count()) {
            foreach ($menu_items as $k => $menu_item) {
                $menu_item->children = collect([]);

                foreach ($menu_items as $i => $menu_subitem) {
                    if ($menu_subitem->parent_id == $menu_item->id) {
                        $menu_item->children->push($menu_subitem);

                        // remove the subitem for the first level
                        $menu_items = $menu_items->reject(function ($item) use ($menu_subitem) {
                            return $item->id == $menu_subitem->id;
                        });
                    }
                }
            }
        }

        return $menu_items;
        // return $menu->menu_items()->getTree();
    }

    private function registerEnumWithDoctrine()
    {
        DB::getDoctrineSchemaManager()
            ->getDatabasePlatform()
            ->registerDoctrineTypeMapping('enum', 'string');
    }    
}
