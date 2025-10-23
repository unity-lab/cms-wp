<?php
namespace Sabberworm\CSS;
if (!defined('ABSPATH')) exit;
interface Renderable
{
 public function __toString();
 public function render($oOutputFormat);
 public function getLineNo();
}
