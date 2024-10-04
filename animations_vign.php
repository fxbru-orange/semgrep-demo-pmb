<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: animations_vign.php,v 1.1.4.1 2024/03/14 09:46:51 qvarin Exp $

use Pmb\Animations\Models\AnimationModel;
use Pmb\Animations\Orm\AnimationOrm;

global $class_path, $base_path, $base_auth, $base_title, $base_noheader, $base_nocheck, $base_nobody;
global $no_caching, $type, $id, $msg;

$base_path     = ".";
$base_auth     = ""; //"CIRCULATION_AUTH";
$base_title    = "";
$base_noheader = 1;
//$base_nocheck  = 1;
$base_nobody   = 1;

require_once($base_path."/includes/init.inc.php");
require_once($class_path."/curl.class.php");
require_once("$base_path/includes/isbn.inc.php");
require_once($base_path."/admin/connecteurs/in/amazon/amazon.class.php");

session_write_close();

global $animationId, $size;
$animationId = intval($animationId);
$size = intval($size);

if (AnimationOrm::exist($animationId)) {
    AnimationModel::printLogo($animationId,$size);
} else {
    http_response_code(404);
    $img = imagecreatetruecolor(1,1);
    header('Content-Type: image/png');
    imagepng($img);
}
