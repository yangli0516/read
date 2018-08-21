<?php
/**
 * This file is part of the Research Environment for Ancient Documents (READ). For information on the authors
 * and copyright holders of READ, please refer to the file AUTHORS in this distribution or
 * at <https://github.com/readsoftware>.
 *
 * READ is free software: you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software Foundation, either version 3 of the License,
 * or (at your option) any later version.
 *
 * READ is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with READ.
 * If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * validateFreeTextLine
 *
 * given an freetext line sequence ID and 'FreeText' the service saves and validates  the freetext returning
 * the information as an update to the client data.
 *
 * @author     Stephen White  <stephenawhite57@gmail.com>
 * @copyright  @see AUTHORS in repository root <https://github.com/readsoftware/read>
 * @link       https://github.com/readsoftware
 * @version    1.0
 * @license    @see COPYING in repository root or <http://www.gnu.org/licenses/>
 * @package    READ Research Environment for Ancient Documents
 * @subpackage Services
 */
  define('ISSERVICE', 1);
  ini_set("zlib.output_compression_level", 5);
  ob_start('ob_gzhandler');

  header("Content-type: text/javascript");
  header('Cache-Control: no-cache');
  header('Pragma: no-cache');

  require_once dirname(__FILE__) . '/../common/php/DBManager.php';
  require_once dirname(__FILE__) . '/../common/php/userAccess.php';
  require_once dirname(__FILE__) . '/../common/php/utils.php';
  require_once dirname(__FILE__) . '/../model/utility/parser.php';
  require_once dirname(__FILE__) . '/clientDataUtils.php';
  
  $dbMgr = new DBManager();
  $retVal = array();
  $errors = array();
  $entities = array();
  $warnings = array();
if (!array_key_exists('freetext', $_POST)) {
    array_push($errors, "invalid json data");
} else {
    $freetext = $_POST['freetext'];
    $parserConfigs = array(
    createParserConfig(2, "{2}", '{1}', "TEST", "guest", null, null, "1", 1, null, $freetext)
    );
    $parser = new Parser($parserConfigs);
    $parser->setBreakOnError(true);
    $parser->parse();
    $errStr = null;
    if (count($parser->getErrors())) {
        foreach ($parser->getErrors() as $error) {
            if (preg_match('/(?:at|for)?\s?character (\d+)/', $error, $matches)) {
                $errIndex = $matches[1];
                $errStr = "&nbsp;&nbsp;".mb_substr($freetext, 0, $errIndex).
                "<span class=\"errhilite\">".mb_substr($freetext, $errIndex, 1)." </span>".
                "<span class=\"errmsg\">  error: ".
                mb_substr($error, 0, mb_strpos($error, $matches[0])) ."</span>";
                $errStr = preg_replace("/%20/", " ", $errStr);
                break;
            }
        }
    }
    if (array_key_exists('seqID', $_POST)) {
        $freeTextLine = new Sequence($_POST['seqID']);
        if ($freeTextLine->hasError()) {
            array_push($errors, "unable to open sequence - ".$freeTextLine->getErrors(true));
        } else {
            $freeTextLine->storeScratchProperty('freetext', $freetext);
            // if err will store, else errStr is null will remove old err
            $freeTextLine->storeScratchProperty('validationMsg', $errStr);
            $freeTextLine->save();
            addUpdateEntityReturnData(
                'seq', $freeTextLine->getID(), 
                'freetext', $freeTextLine->getScratchProperty('freetext')
            );
            addUpdateEntityReturnData(
                'seq', $freeTextLine->getID(), 
                'validationMsg', $freeTextLine->getScratchProperty('validationMsg')
            );
        }
    }
}

  $retVal["success"] = false;
if (count($errors)) {
    $retVal["errors"] = $errors;
} else {
    $retVal["success"] = true;
}
if (count($warnings)) {
    $retVal["warnings"] = $warnings;
}
if ($errStr) {
    $retVal["errString"] = $errStr;
}
if (count($entities)) {
    $retVal["entities"] = $entities;
}
if (array_key_exists("callback", $_REQUEST)) {
    $cb = $_REQUEST['callback'];
    if (strpos("YUI", $cb) == 0) { // YUI callback need to wrap
        print $cb."(".json_encode($retVal).");";
    }
} else {
    print json_encode($retVal);
}
