<?php

namespace Flynsarmy\RelatedRepeater\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Backend\Widgets\Form;

/**
 * Options Back-end Controller
 */
class Options extends Controller
{
    public $implement = [
        'Backend.Behaviors.ListController',
        'Backend.Behaviors.FormController',
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Flynsarmy.RelatedRepeater', 'options', 'options');
    }
}
