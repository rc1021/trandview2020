<?php

/**
 * Laravel-admin - admin builder based on Laravel.
 * @author z-song <https://github.com/z-song>
 *
 * Bootstraper for Admin.
 *
 * Here you can remove builtin form field:
 * Encore\Admin\Form::forget(['map', 'editor']);
 *
 * Or extend custom form field:
 * Encore\Admin\Form::extend('php', PHPEditor::class);
 *
 * Or require js and css assets:
 * Admin::css('/packages/prettydocs/css/styles.css');
 * Admin::js('/packages/prettydocs/js/main.js');
 *
 */

Encore\Admin\Form::forget(['map', 'editor']);

Encore\Admin\Facades\Admin::favicon('/favicon.ico');
Encore\Admin\Facades\Admin::js('/js/admin.js');
Encore\Admin\Facades\Admin::style('.modal-body {max-height: calc(100vh - 143px);overflow-y: auto;}');

Encore\Admin\Form::extend('linenotify', App\Admin\Extensions\Form\LINENotifyBinder::class);
