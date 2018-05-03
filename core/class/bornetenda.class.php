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

class bornetenda extends eqLogic {
    /*     * *************************Attributs****************************** */

    /*     * ***********************Methode static*************************** */

    public static function pull() {
        log::add('bornetenda','debug','cron start');
        foreach (self::byType('bornetenda') as $eqLogic) {
            $eqLogic->scan();
        }
        log::add('bornetenda','debug','cron stop');
    }

    public function getUrl() {
        $url = 'http://';
        if ( $this->getConfiguration('username') != '' )
        {
            $url .= $this->getConfiguration('username').':'.$this->getConfiguration('password').'@';
        }
        $url .= $this->getConfiguration('ip');
        return $url."/";
    }

    public function preUpdate()
    {
        $reboot = $this->getCmd(null, 'reboot');
         if ( ! is_object($reboot) ) {
            $reboot = new bornetendaCmd();
            $reboot->setName('Reboot');
            $reboot->setEqLogic_id($this->getId());
            $reboot->setType('action');
            $reboot->setSubType('other');
            $reboot->setLogicalId('reboot');
            $reboot->setEventOnly(1);
            $reboot->setIsVisible(0);
            $reboot->setDisplay('generic_type','GENERIC_ACTION');
            $reboot->save();
        }
        else
        {
            if ( $reboot->getDisplay('generic_type') == "" )
            {
                $reboot->setDisplay('generic_type','GENERIC_ACTION');
                $reboot->save();
            }
        }
        $cmd = $this->getCmd(null, 'status');
        if ( is_object($cmd) ) {
            if ( $cmd->getDisplay('generic_type') == "" )
            {
                $cmd->setDisplay('generic_type','GENERIC_INFO');
                $cmd->save();
            }
        }
        $cmd = $this->getCmd(null, 'wifistatus');
        if ( is_object($cmd) ) {
            if ( $cmd->getDisplay('generic_type') == "" )
            {
                $cmd->setDisplay('generic_type','GENERIC_INFO');
                $cmd->save();
            }
        }
        $cmd = $this->getCmd(null, 'wifi_off');
        if ( is_object($cmd) ) {
            if ( $cmd->getDisplay('generic_type') == "" )
            {
                $cmd->setDisplay('generic_type','GENERIC_ACTION');
                $cmd->save();
            }
        }
        $cmd = $this->getCmd(null, 'wifi_on');
        if ( is_object($cmd) ) {
            if ( $cmd->getDisplay('generic_type') == "" )
            {
                $cmd->setDisplay('generic_type','GENERIC_ACTION');
                $cmd->save();
            }
        }

        if ( $this->getIsEnable() )
        {
            log::add('bornetenda','debug','get '.preg_replace("/:[^:]*@/", ":XXXX@", $this->getUrl()). 'system_status.asp');
            $info = @file_get_contents($this->getUrl(). 'system_status.asp');
            if ( $info === false )
                throw new Exception(__('La borne tenda ne repond pas ou le compte est incorrecte.',__FILE__));
        }
    }

    public function preInsert()
    {
        $this->setIsVisible(0);
    }

    public function postInsert()
    {
        $cmd = $this->getCmd(null, 'status');
        if ( ! is_object($cmd) ) {
            $cmd = new bornetendaCmd();
            $cmd->setName('Etat');
            $cmd->setEqLogic_id($this->getId());
            $cmd->setType('info');
            $cmd->setSubType('binary');
            $cmd->setLogicalId('status');
            $cmd->setIsVisible(1);
            $cmd->setEventOnly(1);
            $cmd->save();
        }
        $wifi_off = $this->getCmd(null, 'wifi_off');
        if ( ! is_object($wifi_off) ) {
            $wifi_off = new bornetendaCmd();
            $wifi_off->setName('Wifi Off');
            $wifi_off->setEqLogic_id($this->getId());
            $wifi_off->setType('action');
            $wifi_off->setSubType('other');
            $wifi_off->setLogicalId('wifi_off');
            $wifi_off->setEventOnly(1);
            $wifi_off->setDisplay('generic_type','GENERIC_ACTION');
            $wifi_off->save();
        }
        $wifi_on = $this->getCmd(null, 'wifi_on');
        if ( ! is_object($wifi_on) ) {
            $wifi_on = new bornetendaCmd();
            $wifi_on->setName('Wifi On');
            $wifi_on->setEqLogic_id($this->getId());
            $wifi_on->setType('action');
            $wifi_on->setSubType('other');
            $wifi_on->setLogicalId('wifi_on');
            $wifi_on->setEventOnly(1);
            $wifi_on->setDisplay('generic_type','GENERIC_ACTION');
            $wifi_on->save();
        }
        $reboot = $this->getCmd(null, 'reboot');
         if ( ! is_object($reboot) ) {
            $reboot = new bornetendaCmd();
            $reboot->setName('Reboot');
            $reboot->setEqLogic_id($this->getId());
            $reboot->setType('action');
            $reboot->setSubType('other');
            $reboot->setLogicalId('reboot');
            $reboot->setEventOnly(1);
            $reboot->setIsVisible(0);
            $reboot->setDisplay('generic_type','GENERIC_ACTION');
            $reboot->save();
        }
        $wifistatus = $this->getCmd(null, 'wifistatus');
        if ( ! is_object($wifistatus)) {
            $wifistatus = new bornetendaCmd();
            $wifistatus->setName('Etat Wifi');
            $wifistatus->setEqLogic_id($this->getId());
            $wifistatus->setLogicalId('wifistatus');
            $wifistatus->setUnite('');
            $wifistatus->setType('info');
            $wifistatus->setSubType('binary');
            $wifistatus->setIsHistorized(0);
            $wifistatus->setEventOnly(1);
            $wifistatus->setDisplay('generic_type','GENERIC_INFO');
            $wifistatus->save();
        }
    }

    public function event() {
        foreach (eqLogic::byType('bornetenda') as $eqLogic) {
            if ( $eqLogic->getId() == init('id') ) {
                $eqLogic->scan();
            }
        }
    }

    public function scan() {
        if ( $this->getIsEnable() ) {
            log::add('bornetenda','debug','scan '.$this->getName());
            $statuscmd = $this->getCmd(null, 'status');
            $url = $this->getUrl();
            log::add('bornetenda','debug','get '.preg_replace("/:[^:]*@/", ":XXXX@", $url).'wireless_basic.asp');
            $info = @file_get_contents($this->getUrl(). 'wireless_basic.asp');
            if ( $info === false ) {
                throw new Exception(__('La borne tenda ne repond pas.',__FILE__));
                if ($statuscmd->execCmd() != 0) {
                    $statuscmd->setCollectDate('');
                    $statuscmd->event(0);
                }
            }
            if ( preg_match("/var radio_off = '(.*)';/", $info, $regs) == 1 ) {
                $wifistatus = $this->getCmd(null, 'wifistatus');
                log::add('bornetenda','debug','wifistatus change : '.$wifistatus->execCmd()." != ".$wifistatus->formatValue($regs[1]));
                if ( $wifistatus->execCmd() != $wifistatus->formatValue($regs[1]) ) {
                    log::add('bornetenda','debug','wifistatus change : '.$regs[1]);
                    $wifistatus->setCollectDate('');
                    $wifistatus->event($regs[1]);
                }
            }
            if ($statuscmd->execCmd() != 1) {
                $statuscmd->setCollectDate('');
                $statuscmd->event(1);
            }
            log::add('bornetenda','debug','scan end '.$this->getName());
        }
    }
    /*     * **********************Getteur Setteur*************************** */
}

class bornetendaCmd extends cmd
{
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */


    /*     * *********************Methode d'instance************************* */
    public function formatValue($_value, $_quote = false) {
        if ($this->getLogicalId() == 'wifistatus') {
            if ( $_value == 1 ) {
                return 0;
            } else {
                return 1;
            }
        }
        return $_value;
    }
    /*     * **********************Getteur Setteur*************************** */
    public function execute($_options = null) {
        $eqLogic = $this->getEqLogic();
        if (!is_object($eqLogic) || $eqLogic->getIsEnable() != 1) {
            throw new Exception(__('Equipement desactivé impossible d\éxecuter la commande : ' . $this->getHumanName(), __FILE__));
        }
        $url = $eqLogic->getUrl();

        if ( $this->getLogicalId() == 'wifi_off' )
        {
            $url .= 'goform/wirelessBasic?radiohiddenButton=1';
        }
        else if ( $this->getLogicalId() == 'wifi_on' )
        {
            $url .= 'goform/wirelessBasic?radiohiddenButton=0';
        }
        else if ( $this->getLogicalId() == 'reboot' )
        {
            $url .= "goform/SysToolReboot";
        }
        else
            return false;
        log::add('bornetenda','debug','get '.preg_replace("/:[^:]*@/", ":XXXX@", $url));
        $result = @file_get_contents($url);
        if ( $result === false )
        {
            return false;
        }
        $eqLogic->scan();
        return false;
    }

    public function imperihomeGenerate($ISSStructure) {
        if ( $this->getLogicalId() == 'wifistatus' ) {
            $type = 'DevSwitch';
        }
        elseif ( $this->getLogicalId() == 'status' ) {
            $type = 'DevSwitch';
        }
        else {
            return $info_device;
        }
        $eqLogic = $this->getEqLogic(); // Récupération de l'équipement de la commande
        $object = $eqLogic->getObject(); // Récupération de l'objet de l'équipement

        // Construction de la structure de base
        $info_device = array(
        'id' => $this->getId(), // ID de la commande, ne pas mettre autre chose!
        'name' => $eqLogic->getName()." - ".$this->getName(), // Nom de l'équipement que sera affiché par Imperihome: mettre quelque chose de parlant...
        'room' => (is_object($object)) ? $object->getId() : 99999, // Numéro de la pièce: ne pas mettre autre chose que ce code
        'type' => $type, // Type de l'équipement à retourner (cf ci-dessus)
        'params' => array(), // Le tableau des paramètres liés à ce type (qui sera complété aprés.
        );
        #$info_device['params'] = $ISSStructure[$info_device['type']]['params']; // Ici on vient copier la structure type: laisser ce code

        if ( $this->getLogicalId() == 'wifistatus' ) { // Sauf si on est entrain de traiter la commande "Mode", à ce moment là on indique un autre type
            array_push ($info_device['params'], array("value" =>  '#' . $eqLogic->getCmd(null, 'wifistatus')->getId() . '#', "key" => "status", "type" => "infoBinary", "Description" => "Current status : 1 = On / 0 = Off"));
            $info_device['actions']["setStatus"]["item"]["0"] = $eqLogic->getCmd(null, 'wifi_off')->getId();
            $info_device['actions']["setStatus"]["item"]["1"] = $eqLogic->getCmd(null, 'wifi_on')->getId();
        }
        elseif ( $this->getLogicalId() == 'status' ) { // Sauf si on est entrain de traiter la commande "Mode", à ce moment là on indique un autre type
            array_push ($info_device['params'], array("value" =>  '#' . $eqLogic->getCmd(null, 'status')->getId() . '#', "key" => "status", "type" => "infoBinary", "Description" => "Current status : 1 = On / 0 = Off"));
            $info_device['actions']["setStatus"]["item"]["0"] = $eqLogic->getCmd(null, 'reboot')->getId();
            $info_device['actions']["setStatus"]["item"]["1"] = "";
        }
        // Ici on traite les autres commandes (hors "Mode")
        return $info_device;
    }
}
