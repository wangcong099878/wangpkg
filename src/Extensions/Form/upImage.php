<?php


namespace Wang\Pkg\Extensions\Form;

use Dcat\Admin\Form\Field;

class upImage extends Field
{

    protected $view = 'wangpkg::admin.form.upImage';

    protected static $css = [];

    protected static $js = [
    ];

    public function render()
    {
        $this->variables['id'] = uniqid();
        return parent::render();
    }

    public function setValue($value = '')
    {
        if ($value) {
            $this->value = $value;
        }
        return $this;
    }
}
