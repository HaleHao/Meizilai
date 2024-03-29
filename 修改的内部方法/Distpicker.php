<?php

namespace Encore\ChinaDistpicker;

use Encore\Admin\Form\Field;
use Illuminate\Support\Arr;

class Distpicker extends Field
{
    /**
     * @var string
     */
    protected $view = 'laravel-admin-china-distpicker::select';

    /**
     * @var array
     */
    protected static $js = [
        'vendor/laravel-admin-ext/china-distpicker/dist/distpicker.min.js'
    ];

    /**
     * @var array
     */
    protected $columnKeys = ['province', 'city', 'district'];

    /**
     * @var array
     */
    protected $placeholder = [];

    /**
     * Distpicker constructor.
     *
     * @param array $column
     * @param array $arguments
     */
    public function __construct($column, $arguments)
    {
        if (!Arr::isAssoc($column)) {
            $this->column = array_combine($this->columnKeys, $column);
        } else {
            $this->column      = array_combine($this->columnKeys, array_keys($column));
            $this->placeholder = array_combine($this->columnKeys, $column);
        }

        $this->label = empty($arguments) ? '地区选择' : current($arguments);
    }

    /**
     * @param int $count
     * @return $this
     */
    public function autoselect($count = 0)
    {
        return $this->attribute('data-autoselect', $count);
    }

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        $province = old($this->column['province'], array_get($this->value, 'province')) ?: array_get($this->placeholder, 'province');
        $city     = old($this->column['city'],     array_get($this->value, 'city'))     ?: array_get($this->placeholder, 'city');
        $district = old($this->column['district'], array_get($this->value, 'district')) ?: array_get($this->placeholder, 'district');

        $id = 'distpicker-' . uniqid();

        $this->script = <<<EOT
var pro = '$province';
var ct = '$city';
var dis = '$province';
$('#pro').val(pro);
$('#ct').val(ct);
$('#dis').val(dis   );
$("#{$id}").distpicker({
  province: '$province',
  city: '$city',
  district: '$district',
});
$('#{$id} select').change(function(event) {
     $('.hideinputbox input').eq($(this).index()).val($(this).val());             
     console.log($('.hideinputbox input').eq($(this).index()).val());
});
EOT;

        return parent::render()->with(compact('id'));
    }
}