<?php

defined('ABSPATH') || exit;
?>
<script type="text/html" id="tmpl-rtwpvg-image">
    <# hasVideo = ( typeof data.rtwpvg_video_link !== 'undefined' && data.rtwpvg_video_link != ''  ) ? 'video' : '';  #>
    <li class="image {{hasVideo}}">
        <input type="hidden" name="rtwpvg[{{data.product_variation_id}}][]" value="{{data.id}}">
        <img src="{{data.url}}">
        <a href="#" class="delete rtwpvg-remove-image"><span class="dashicons dashicons-dismiss"></span></a>
    </li>
</script>