<?php

namespace App\Admin\Extensions\Tools;

use App\Model\Image;
use Encore\Admin\Admin;
use Encore\Admin\Grid\Displayers\AbstractDisplayer;

class OpenMap extends AbstractDisplayer
{
    public function display(\Closure $callback = null, $btn = '')
    {
        $callback = $callback->bindTo($this->row);

        $html = call_user_func($callback);

        $key = $this->getKey();

        Admin::script($this->script());

        return <<<EOT
<span class="btn btn-xs  grid-open-map" data-key="{$key}" data-toggle="modal" data-target="#grid-modal-{$key}">
     $btn
</span>

<div class="modal" id="grid-modal-{$key}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span></button>
        <h4 class="modal-title">$btn [$key]</h4>
      </div>
      <div class="modal-body">
        $html
      </div>
    </div>
  </div>
</div>
EOT;
    }

    protected function script()
    {
        return <<<EOT

// 其他操作

//$('.grid-open-map').on('click', function() {
//
//    var key = $(this).data('key');
//
//    var container = document.getElementById("grid-map-"+key);
//    var map = new qq.maps.Map(container, {
//        zoom: 13
//    });
//
//    var marker = new qq.maps.Marker({
//        draggable: true,
//        map: map
//    });
//});

EOT;
    }
}