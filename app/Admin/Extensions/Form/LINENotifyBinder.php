<?php

namespace App\Admin\Extensions\Form;

use Encore\Admin\Form\Field;
use Encore\Admin\Form\Field\PlainInput;

class LINENotifyBinder extends Field
{
    use PlainInput;

    private $icon = 'fa-bell';

    protected $view = 'admin.encore.form.line-notify-binder';

    protected static $css = [
    ];

    protected static $js = [
        'https://cdnjs.cloudflare.com/ajax/libs/inputmask/4.0.9/jquery.inputmask.bundle.min.js'
    ];

    public function icon($icon) {
        $this->icon = $icon;
        return $this;
    }

    public function render()
    {
        $this->initPlainInput();

        $this->prepend("<i class='fa {$this->icon}'></i>")
            ->defaultAttribute('type', 'text')
            ->defaultAttribute('id', $this->id)
            ->defaultAttribute('name', $this->elementName ?: $this->formatName($this->column))
            ->defaultAttribute('value', old($this->column, $this->value()))
            ->defaultAttribute('class', 'form-control '.$this->getElementClassString())
            ->defaultAttribute('placeholder', $this->getPlaceholder());

        $status = $this->value()?'取消綁定':'綁定';
        $this->append("<a class='btn btn-default' href='javascript:;' onclick='oAuth2();' type='button'>{$status}</a>")
            ->defaultAttribute('grouptype', 'btn');

        $this->addVariables([
            'prepend' => $this->prepend,
            'append'  => $this->append,
            'cancelable' => $this->value()? 'true' : 'false'
        ]);

        $this->script = <<<EOT
EOT;

        return parent::render();
    }
}
