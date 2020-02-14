<?php
/**
* Tilde_tts
* @package project
* @author Wizard <sergejey@gmail.com>
* @copyright http://majordomo.smartliving.ru/ (c)
* @version 0.1 (wizard, 01:02:37 [Feb 14, 2020])
*/
//
//
class tilde_tts extends module {
/**
* tilde_tts
*
* Module class constructor
*
* @access private
*/
function __construct() {
  $this->name="tilde_tts";
  $this->title="Tilde_tts";
  $this->module_category="<#LANG_SECTION_APPLICATIONS#>";
  $this->checkInstalled();
}
/**
* saveParams
*
* Saving module parameters
*
* @access public
*/
function saveParams($data=1) {
 $p=array();
 if (IsSet($this->id)) {
  $p["id"]=$this->id;
 }
 if (IsSet($this->view_mode)) {
  $p["view_mode"]=$this->view_mode;
 }
 if (IsSet($this->edit_mode)) {
  $p["edit_mode"]=$this->edit_mode;
 }
 if (IsSet($this->tab)) {
  $p["tab"]=$this->tab;
 }
 return parent::saveParams($p);
}
/**
* getParams
*
* Getting module parameters from query string
*
* @access public
*/
function getParams() {
  global $id;
  global $mode;
  global $view_mode;
  global $edit_mode;
  global $tab;
  if (isset($id)) {
   $this->id=$id;
  }
  if (isset($mode)) {
   $this->mode=$mode;
  }
  if (isset($view_mode)) {
   $this->view_mode=$view_mode;
  }
  if (isset($edit_mode)) {
   $this->edit_mode=$edit_mode;
  }
  if (isset($tab)) {
   $this->tab=$tab;
  }
}
/**
* Run
*
* Description
*
* @access public
*/
function run() {
 global $session;
  $out=array();
  if ($this->action=='admin') {
   $this->admin($out);
  } else {
   $this->usual($out);
  }
  if (IsSet($this->owner->action)) {
   $out['PARENT_ACTION']=$this->owner->action;
  }
  if (IsSet($this->owner->name)) {
   $out['PARENT_NAME']=$this->owner->name;
  }
  $out['VIEW_MODE']=$this->view_mode;
  $out['EDIT_MODE']=$this->edit_mode;
  $out['MODE']=$this->mode;
  $out['ACTION']=$this->action;
  $this->data=$out;
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  $this->result=$p->result;
}
/**
* BackEnd
*
* Module backend
*
* @access public
*/
function admin(&$out) {
  $this->getConfig();
	$out['DISABLED'] = $this->config['DISABLED'];
	switch($this->view_mode) {
		case 'update_settings':

			global $disabled;
			$this->config['DISABLED'] = $disabled;
			$this->saveConfig();
			$this->redirect('?view_mode=ok');

	}
}
/**
* FrontEnd
*
* Module frontend
*
* @access public
*/
function usual(&$out) {
 $this->admin($out);
}
function processSubscription($event, &$details) {
 $this->getConfig();
  $level=$details['level'];
  $message=$details['message'];
  $destination = $details['destination'];
  $filename       = md5($message) . '_tilde.mp3';
  $cachedVoiceDir = ROOT . 'cms/cached/voice';
  $cachedFileName = $cachedVoiceDir . '/' . $filename;


  if (($event=='SAY' || $event=='SAYTO' || $event=='ASK') && !$this->config['DISABLED'] && !$details['ignoreVoice']) {

    $base_url       = 'https://runa.tilde.lv/client/say/?text=';
    $newmessage = str_replace(' ', '%20', $message);
    $payload = $base_url.$newmessage;
       if (!file_exists($cachedFileName))
       {
       {
          $ch = curl_init();
            curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $payload);
            $response = curl_exec($ch);
            file_put_contents($cachedFileName , $response);
            curl_close($ch);
      }
       } else {
        @touch($cachedFileName);
       }

       if (file_exists($cachedFileName)) {
   processSubscriptionsSafe('SAY_CACHED_READY', array(
       'level' => $level,
       'tts_engine' => 'tilde_tts',
       'filename' => $cachedFileName,
       'destination' => $destination,
       'event' => $event,
       'message' => $message,
   ));
   if ($event=='SAY' && $level >= (int)getGlobal('minMsgLevel')) {
       playSound($cachedFileName, 1, $level);
     }
         //$details['ignoreVoice']=1;

       }


 }
}
/**
* Install
*
* Module installation routine
*
* @access private
*/
 function install($data='') {
        subscribeToEvent($this->name, 'SAY', '', 100);
        subscribeToEvent($this->name, 'SAYTO', '', 100);
        subscribeToEvent($this->name, 'ASK', '', 100);
 		    subscribeToEvent($this->name, 'SAYREPLY', '', 100);
  parent::install();
 }
 /**
     * Uninstall
     *
     * Module deinstallation routine
     *
     * @access private
     */
    function uninstall()
    {
        unsubscribeFromEvent($this->name, 'SAY');
        unsubscribeFromEvent($this->name, 'SAYTO');
        unsubscribeFromEvent($this->name, 'ASK');
        unsubscribeFromEvent($this->name, 'SAYREPLY');
        parent::uninstall();
    }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgRmViIDE0LCAyMDIwIHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
