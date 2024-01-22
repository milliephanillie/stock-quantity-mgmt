<?php
namespace StockMGMT\Admin;

use StockMGMT\Admin\Menu\SQMGMT_AdminMenu;

class SQMGMT_BaseAdmin {
    public function __construct() {
        $admin_menu = new SQMGMT_AdminMenu();
        $admin_menu->register_hooks();
    }
}
