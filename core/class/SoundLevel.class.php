<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class SoundLevel extends eqLogic {
    /*     * *************************Attributs****************************** */



    /*     * ***********************Methode static*************************** */

    /*
     * Fonction exécutée automatiquement toutes les minutes par Jeedom
     */
    public static function cron() {
        foreach (eqLogic::byType('SoundLevel', true) as $eqLogic) {
            $eqLogic->updateInfo();
        }
    }

    /*     * *********************Méthodes d'instance************************* */

    public function preInsert() {

    }

    public function postInsert() {

    }

    public function preSave() {

    }

    public function postSave() {
        $cmd = $this->getCmd(null, 'niveau');
        if (!is_object($cmd)) {
            $cmd = new SoundLevel();
            $cmd->setLogicalId('niveau');
            $cmd->setIsVisible(1);
            $cmd->setName(__('niveau', __FILE__));
        }
        $cmd->setType('info');
        $cmd->setSubType('string');
        $cmd->setEqLogic_id($this->getId());
        $cmd->setDisplay('generic_type', 'ENERGY_STATE');
        $cmd->save();

        $infos = $this->getInfo();
        $this->updateInfo();
    }

    public function preUpdate() {
      if ($this->getConfiguration('carte audio') == '') {
                  throw new Exception(__('La carte audio doit etre renseignée, tapez la commande arecord -l pour identifier', __FILE__));
              }
      if ($this->getConfiguration('duration') == '') {
                  throw new Exception(__('Veuillez entrer la durée denregistrement', __FILE__));
              }
    }

    public function postUpdate() {

    }

    public function preRemove() {

    }

    public function postRemove() {

    }

    public function getInfo() {
        $this->checkSoundLevelStatus();

        $niveau=shell_exec("sudo sh ../../3rparty/SPL.sh".$this->getConfiguration('carte audio')." ".$this->getConfiguration('duration'));

        return array('niveau' => $niveau);
    }

    public function updateInfo() {
        try {
            $infos = $this->getInfo();
        } catch (Exception $e) {
            return;
        }

        if (!is_array($infos)) {
            return;
        }

        if (isset($infos['niveau'])) {
            $this->checkAndUpdateCmd('niveau', $infos['niveau']);
        }

        throw new Exception(var_dump($infos), 1);
    }

    public function checkSoundLevelStatus() {
      $check=shell_exec("sudo aplay -l");
      echo $check;
      if(strstr($check, "no soundcards found")){
          throw new Exception("Aucune carte son detecté", 1);
      }
    }

    /*     * **********************Getteur Setteur*************************** */
}

class SoundLevel extends cmd {
  public function execute($_options = array()) {
      $eqLogic = $this->getEqLogic();

      $eqLogic->checkSoundLevelStatus();

      $eqLogic->updateInfo();
  }
}
