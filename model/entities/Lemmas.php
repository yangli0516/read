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
  * Classes to deal with Lemma entities
  *
  * @author      Stephen White  <stephenawhite57@gmail.com>
  * @copyright   @see AUTHORS in repository root <https://github.com/readsoftware/read>
  * @link        https://github.com/readsoftware
  * @version     1.0
  * @license     @see COPYING in repository root or <http://www.gnu.org/licenses/>
  * @package     READ Research Environment for Ancient Documents
  * @subpackage  Entity Classes
  */
  require_once (dirname(__FILE__) . '/EntityIterator.php');
  require_once (dirname(__FILE__) . '/Lemma.php');

//*******************************************************************
//*********************   LEMMA CLASS   *****************************
//*******************************************************************
/**
  * Lemmas class which is an iterating container of lemmas
  *
  * <code>
  * require_once 'Lemmas.php';
  *
  * $lemmas = new Lemmas(10,5);
  * $lemma = $lemmas->current();
  * $key = $lemmas->key();
  * echo " lemma $key is ".$lemma->getLemma();
  * </code>
  *
  * @author      Stephen White  <stephenawhite57@gmail.com>
  */

  class Lemmas extends EntityIterator {

    //*******************************PRIVATE MEMBERS************************************

    //****************************CONSTRUCTOR FUNCTION***************************************

    /**
    * Create an Lemmas iterator, optionally setting the offset and pagesize
    * @param int $pageSize sets the max size for query results (default 20)
    * @param int $offset sets the start point for query results (default 0)
    * @todo write a store procedure to test for intersection of 2 integer arrays for security checking access IDs with VisibilityIDs
    * @todo add code to load all ??? what about 10k or 100k case (could call to get rownum)
    */
    public function __construct( $condition = "", $sort = "lem_id", $offset = 0, $pageSize = 20) {
      parent::__construct("lemma","lem_id");
      $this->_autoAdvancePage = true;
      $this->_pageSize = $pageSize;
      $this->_offset = $offset;
      if ($condition) $this->_condition = $condition;
//      $this->_security = isSysAdmin()?null:" (".getUserID()."= lem_owner_id or ".getUserID()." = ANY (\"lem_visibility_ids\"))";
      $this->_security = parent::getEntityAccessCondition("lem");
      $this->_sort = $sort;
      $this->_dbMgr = new DBManager();
      $this->loadEntities();
    }

    //*******************************PUBLIC FUNCTIONS************************************


    /**
    * Lemmas - array of lemmas from the current query
    *
    * @return array returns a Lemma array for the current page size (default is 20)
    */
    public function getLemmas() {
      return $this->_entities;
    }

    public function createObject($arg){
      return new Lemma($arg);
    }

   }
?>
