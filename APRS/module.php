<?php
include_once("APRSCommon.php");
include_once("APRSProcessData.php");
include_once("COMMON.php");

class APRS extends IPSModule {

	use APRSCommon;
	use APRSProcessData;
	use COMMON;

	const CATEGORY_NAME_DataViewer = "DataViewer";
	const CATEGORY_NAME_Notifications = "Notifications";
	const CATEGORY_NAME_MinMax = "MinMax";
	const DUMMY_NAME_MinMax = "MinMaxData";

	private $logLevel = 3;
	private $logCnt = 0;
	private $enableIPSLogOutput = false;
	
	public function __construct($InstanceID) {

		parent::__construct($InstanceID);		// Diese Zeile nicht löschen

		$this->logLevel = @$this->ReadPropertyInteger("LogLevel");
		if ($this->logLevel >= LogLevel::TRACE) {
			$this->AddLog(__FUNCTION__, sprintf("Log-Level is %d", $this->logLevel));
		}
	}

	public function Create() {
		parent::Create();				//Never delete this line!

		$logMsg = sprintf("Create Modul '%s [%s]'...", IPS_GetName($this->InstanceID), $this->InstanceID);
		if ($this->logLevel >= LogLevel::INFO) {
			$this->AddLog(__FUNCTION__, $logMsg);
		}
		IPS_LogMessage(__CLASS__ . "_" . __FUNCTION__, $logMsg);

		$logMsg = sprintf("KernelRunlevel '%s'", IPS_GetKernelRunlevel());
		if ($this->logLevel >= LogLevel::DEBUG) {
			$this->AddLog(__FUNCTION__, $logMsg);
		}

		$this->ConnectParent("{3CFF0FD9-E306-41DB-9B5A-9D06D38576C3}");

		$this->RegisterPropertyBoolean('AutoUpdate', false);
		$this->RegisterPropertyInteger("TimerInterval", 15000);
		$this->RegisterPropertyInteger("LogLevel", LogLevel::INFO);

		//$this->RegisterPropertyString("aprsLogin", "user PG1ADW-3 pass 22195 vers IPSOCI 0.1 filter s/O -m/200");
		//$this->RegisterPropertyString("aprsNetbeacon", "PG1ADW-3>APNL51,TCPIP*,qAI,PG1ADW-2:!4819.54N/01425.57E`iGate PG1ADW RX@OCIvpc");

		$this->RegisterPropertyString("aprsLogin", "user PG1ADW-4 pass 22195 vers IPSOCI 0.2 filter s/O -m/200");
		$this->RegisterPropertyString("aprsNetbeacon", "PG1ADW-4>APNL51,TCPIP*,qAI,PG1ADW-2:!4819.54N/01425.57E`iGate PG1ADW RX@OCI");

		$this->RegisterPropertyString("telegramBotToken", "");
		//$this->RegisterPropertyString("telegramChatId", "-4039965244");			//ADW20-Test
		//$this->RegisterPropertyString("telegramChatId", "-459309428");			//ADW20_Radiosonde
		$this->RegisterPropertyString("telegramChatId", "-4026157446");				//ADW20_RadiosondePG1
		
		$this->RegisterTimer('Timer_AutoUpdate_APRS', 0, 'APRS_Timer_AutoUpdate_APRS($_IPS[\'TARGET\']);');
	}

	public function Destroy() {
		IPS_LogMessage(__CLASS__ . "_" . __FUNCTION__, sprintf("Destroy Modul '%s' ...", $this->InstanceID));
		parent::Destroy();						//Never delete this line!
	}

	public function ApplyChanges() {

		parent::ApplyChanges();					//Never delete this line!

		$this->logLevel = $this->ReadPropertyInteger("LogLevel");
		if ($this->logLevel >= LogLevel::INFO) {
			$this->AddLog(__FUNCTION__, sprintf("Set Log-Level to %d", $this->logLevel));
		}

		$this->RegisterProfiles();
		$this->RegisterVariables();


		SetValueString($this->GetIDForIdent("aprsConnectionString"), $this->ReadPropertyString("aprsLogin"));
		SetValueString($this->GetIDForIdent("aprsNetbeacon"), $this->ReadPropertyString("aprsNetbeacon"));
		
		/*
		$connectionState = -1;
		$conID = IPS_GetInstance($this->InstanceID)['ConnectionID'];
		if($conID > 0) {
			$connection = IPS_GetInstance($conID);
			SetValueString($this->GetIDForIdent("aprsServer"), print_r($connection, true));
		} else {
			SetValueString($this->GetIDForIdent("aprsServer"), "not Connected");
		}
		*/

	

		$autoUpdate = $this->ReadPropertyBoolean("AutoUpdate");
		if ($autoUpdate) {
			$timerInterval = $this->ReadPropertyInteger("TimerInterval");
		} else {
			$timerInterval = 0;
		}
		$this->SetUpdateInterval($timerInterval);
	}


	public function Timer_AutoUpdate_APRS() {
		if ($this->logLevel >= LogLevel::DEBUG) { $this->AddLog(__FUNCTION__, "called ...", 0); }
		$this->SendNetbeacon();
	}

	public function SetUpdateInterval(int $timerInterval) {
		if ($timerInterval == 0) {
			if ($this->logLevel >= LogLevel::INFO) {
				$this->AddLog(__FUNCTION__, "Auto-Update stopped [TimerIntervall = 0]", 0);
			}
		} else if ($timerInterval < 10000) {
			$timerInterval = 10000;
			IPS_SetProperty($this->InstanceID, "TimerInterval", $timerInterval);
			if ($this->logLevel >= LogLevel::INFO) {
				$this->AddLog(__FUNCTION__, sprintf("Set Auto-Update Timer Intervall to %sms", $timerInterval), 0);
			}
		} else {
			if ($this->logLevel >= LogLevel::INFO) {
				$this->AddLog(__FUNCTION__, sprintf("Set Auto-Update Timer Intervall to %sms", $timerInterval), 0);
			}
		}
		$this->SetTimerInterval("Timer_AutoUpdate_APRS", $timerInterval);
	}


	public function ConnectAPRS() {
		$aprsLogin = $this->ReadPropertyString("aprsLogin") . "\n";               //"user PG1ADW-3 pass 22195 vers PGx 0.1 filter s/O -m/200\n";
		$aprsNetbeacon = $this->ReadPropertyString("aprsNetbeacon") . "\n";       // "PG1ADW-3>APNL51,TCPIP*,qAI,PG1ADW-2:!4819.54N/01425.57E`iGate PG1ADW Radiosonde-RX@VPC\n";

		$conID = IPS_GetInstance($this->InstanceID)['ConnectionID'];
		if ($conID > 0) {
			$status = IPS_GetInstance($conID)['InstanceStatus'];
			if ($status == 102) {
				if ($this->logLevel >= LogLevel::INFO) {
					$this->AddLog(__FUNCTION__, sprintf("Close Socket: '%s - %s'", $conID, IPS_GetName($conID)), 0);
				}
				IPS_SetProperty($conID, "Open", false);
				IPS_ApplyChanges($conID);
				IPS_Sleep(150);
			}

			if ($this->logLevel >= LogLevel::INFO) {
				$this->AddLog(__FUNCTION__, sprintf("Connect to APRS Network '%s'", "radiosondy.info"), 0);
			}
			IPS_SetProperty($conID, "Open", true);
			IPS_ApplyChanges($conID);
			IPS_Sleep(150);
			$status = IPS_GetInstance($conID)['InstanceStatus'];
			if ($this->logLevel >= LogLevel::INFO) {
				$this->AddLog(__FUNCTION__, sprintf("Connection Status: %s", $status), 0);
			}

			if ($this->logLevel >= LogLevel::INFO) {
				$this->AddLog(__FUNCTION__, sprintf("Do Login  [%s] ...", $aprsLogin), 0);
			}
			$this->Send($aprsLogin);
			IPS_Sleep(150);
			if ($this->logLevel >= LogLevel::DEBUG) {
				$this->AddLog(__FUNCTION__, sprintf("send Netbeacon [%s] ...", $aprsNetbeacon), 0);
			}
			$this->Send($aprsNetbeacon);
		} else {
			if ($this->logLevel >= LogLevel::WARN) {
				$this->AddLog(__FUNCTION__, sprintf("Instanz '%s' has no activ Connection [ConnectionID=%s]", $this->InstanceID, $conID), 0);
			}
		}
	}

	public function SendNetbeacon() {

		$conID = IPS_GetInstance($this->InstanceID)['ConnectionID'];
		if ($conID > 0) {
			$status = IPS_GetInstance($conID)['InstanceStatus'];
			if ($status == 102) {
				$aprsNetbeacon = $this->ReadPropertyString("aprsNetbeacon") . "\n";
				if ($this->logLevel >= LogLevel::INFO) {
					$this->AddLog(__FUNCTION__, $aprsNetbeacon, 0);
				}
				$this->Send($aprsNetbeacon);
			} else {
				if ($this->logLevel >= LogLevel::WARN) {
					$this->AddLog(__FUNCTION__, sprintf("No aktiv Connection [Status: %s] >> do reconnect ...", $status), 0);
				}
				$this->ConnectAPRS();
			}
		}
	}

	public function Send(string $Text) {
		if ($this->logLevel >= LogLevel::COMMUNICATION) {
			$this->AddLog(__FUNCTION__, $Text, 0);
		}
		$this->SendDataToParent(json_encode(array("DataID" => "{79827379-F36E-4ADA-8A95-5F8D1DC92FA9}", "Buffer" => $Text)));
	}

	public function ReceiveData($JSONString) {

		$time_start = microtime(true);

		SetValue($this->GetIDForIdent("receiveCnt"), GetValue($this->GetIDForIdent("receiveCnt")) + 1);
		SetValue($this->GetIDForIdent("lastDataReceived"), time());
		$data = json_decode($JSONString);
		$receivedData = utf8_decode($data->Buffer);

		if ($this->logLevel >= LogLevel::TRACE) { $this->AddLog(__FUNCTION__, $receivedData, 0); }

		$this->ProcessData($receivedData);

		$processingTotalDuration = $this->CalcDuration_ms($time_start);
		SetValue($this->GetIDForIdent("processingTotalDuration"), $processingTotalDuration);
	}

	public function Test() {

		//  "EA1JAY-16>APRRDZ,TCPIP*,qAC,SQ6KXY-1:;T1041007 *161223h4324.41N/00558.28WO/A=000709!weO!Clb=-0.0m/s p=997.5hPa t=10.7C h=65.4% 404.000MHz Type=RS41-SGP TxOff=5h26m rdzTTGOsonde"
		$rawDataTEST = "TEST-12>TEST,qAU,TEST-2:;TEST000 *123456h7890.01N/01234.56E0123/012/A=012345!w2v!Clb=-1.2m/s p=123.4hPa t=-12.3C h=12.3% 123.45MHz Type=RSxx-y TxOff=1h23m Sats=1 FN=1234";
		$this->ProcessData($rawDataTEST);
	}


	public function ResetCounterVariables(string $source) {
		if ($this->logLevel >= LogLevel::INFO) { $this->AddLog(__FUNCTION__, 'RESET CounterVariables'); }
		SetValue($this->GetIDForIdent("dbInsertId"), 0);
		SetValue($this->GetIDForIdent("dbInsertDuration"), 0);
		SetValue($this->GetIDForIdent("receiveCnt"), 0);
		SetValue($this->GetIDForIdent("receivedBytes"), 0);
		SetValue($this->GetIDForIdent("receivedFrames"), 0);
		SetValue($this->GetIDForIdent("processingTotalDuration"), 0);
		SetValue($this->GetIDForIdent("instanzInactivCnt"), 0);
		SetValue($this->GetIDForIdent("lastDataReceived"), 0);

		$scriptId = $this->GetIDForIdent("telegramBot");

		$childIDs = IPS_GetChildrenIDs($scriptId);
		foreach($childIDs as $childId) {
			$objTyp = IPS_GetObject($childId)["ObjectType"];
			if($objTyp == OBJECTTYPE_VARIABLE) {
				SetValue($childId, 0);
			}
		}
	}

	public function ResetDataViewerVariables(string $source) {
		if ($this->logLevel >= LogLevel::INFO) { $this->AddLog(__FUNCTION__, 'RESET DataViewerVariables'); }
		SetValue($this->GetMyVariable("id_dataViewerEnabled"), false);
		SetValue($this->GetMyVariable("id_dataViewer_Distance"), 0);
		SetValue($this->GetMyVariable("id_dataViewer_Altitude"), 0);
		$match1 = GetValue($this->GetMyVariable("id_dataViewer_Match1"));
		if(empty($match1)) {
			SetValue($this->GetMyVariable("id_dataViewer_Match1"), "*:;V* 403.40*MHz*RS41*");
			SetValue($this->GetMyVariable("id_dataViewer_Match2"), "*:;MEA* 403.50*MHz*M20*");
			SetValue($this->GetMyVariable("id_dataViewer_Match3"), "*:;V* 404.10*MHz*RS41*");
		} else {
			SetValue($this->GetMyVariable("id_dataViewer_Match1"), "");
			SetValue($this->GetMyVariable("id_dataViewer_Match2"), "");
			SetValue($this->GetMyVariable("id_dataViewer_Match3"), "");
		}
		SetValue($this->GetMyVariable("id_dataViewer_StopOnNextMatch"), false);
		IPS_SetName($this->GetMyVariable("id_dataViewer"), "Data Viewer");
		SetValue($this->GetMyVariable("id_dataViewerCnt"), 0);
		SetValue($this->GetMyVariable("id_dataViewerCntMoMatch"), 0);
		SetValue($this->GetMyVariable("id_dataViewer"), "");
	}

	public function ResetNotifyVariables(string $source) {
		if ($this->logLevel >= LogLevel::INFO) { $this->AddLog(__FUNCTION__, 'RESET NotifyVariables'); }
		SetValue($this->GetMyVariable("id_notifyEnabled"), false);
		SetValue($this->GetMyVariable("id_notifyDistance"), 400);
		SetValue($this->GetMyVariable("id_notifyAltitude"), 0);
		SetValue($this->GetMyVariable("id_notifyOzon"), false);
		SetValue($this->GetMyVariable("id_notifySondenTyp"), "");
		$match1 = GetValue($this->GetMyVariable("id_notifyMatch1"));
		if(empty($match1)) {
			SetValue($this->GetMyVariable("id_notifyMatch1"), "*:;V* 403.40*MHz*RS41*");
			SetValue($this->GetMyVariable("id_notifyMatch2"), "*:;MEA* 403.50*MHz*M20*");
			SetValue($this->GetMyVariable("id_notifyMatch3"), "*:;V* 404.10*MHz*RS41*");
		} else {
			SetValue($this->GetMyVariable("id_notifyMatch1"), "");
			SetValue($this->GetMyVariable("id_notifyMatch2"), "");
			SetValue($this->GetMyVariable("id_notifyMatch3"), "");
		}
		SetValue($this->GetMyVariable("id_notifyMessage"), "-");
		SetValue($this->GetMyVariable("id_notifyCnt"), 0);
		SetValue($this->GetMyVariable("id_notifyJsonStore"), "");
		SetValue($this->GetMyVariable("id_notifyJsonStoreCnt"), 0);
	}


	public function ResetPG1ADWNotifyVariables(string $source) {
		if ($this->logLevel >= LogLevel::INFO) { $this->AddLog(__FUNCTION__, 'RESET PG1ADWNotifyVariables'); }
		SetValue($this->GetMyVariable("id_notifyPG1ADW"), false);
		SetValue($this->GetMyVariable("id_notifyPG1ADW_Distance"), 20);
		SetValue($this->GetMyVariable("id_notifyPG1ADW_Altitude"), 4000);
		SetValue($this->GetMyVariable("id_notifyPG1ADW_Cnt"), 0);
		SetValue($this->GetMyVariable("id_notifyPG1ADW_Message"), "-");
		SetValue($this->GetMyVariable("id_notifyPG1ADW_JsonStore"), "");
		SetValue($this->GetMyVariable("id_notifyPG1ADW_JsonStoreCnt"), 0);
	}

	public function ResetMinMaxVariables(string $source) {

		$id_minMaxData =  $this->GetMyVariable("id_minMaxData");
		if(!IPS_InstanceExists($id_minMaxData)) {
			$categoryIdMinMax = GetValueInteger($this->GetIDForIdent("categoryIdMinMax"));
			$id_minMaxData = $this->CreateDummyInstance(self::DUMMY_NAME_MinMax, "MinMax Data", $categoryIdMinMax, 615);
			$this->SetMyVariable("id_minMaxData", $id_minMaxData);
		}
		if ($this->logLevel >= LogLevel::INFO) { $this->AddLog(__FUNCTION__, sprintf('RESET MinMaxVariables {DummyId: %s}', $id_minMaxData)); }
		SetValue($this->GetMyVariable("id_minMaxEnabled"), false);
		SetValue($this->GetMyVariable("id_minMax_Distance"), 400);
		SetValue($this->GetMyVariable("id_minMax_Altitude"), 0);
		$match1 = GetValue($this->GetMyVariable("id_minMax_Match1"));
		if(empty($match1)) {
			SetValue($this->GetMyVariable("id_minMax_Match1"), "*:;V* 403.40*MHz*RS41*");
			SetValue($this->GetMyVariable("id_minMax_Match2"), "*:;MEA* 403.50*MHz*M20*");
			SetValue($this->GetMyVariable("id_minMax_Match3"), "*:;V* 404.10*MHz*RS41*");
		} else {
			SetValue($this->GetMyVariable("id_minMax_Match1"), "");
			SetValue($this->GetMyVariable("id_minMax_Match2"), "");
			SetValue($this->GetMyVariable("id_minMax_Match3"), "");
		}

		IPS_SetName($this->GetMyVariable("id_minMaxData"), "MinMax Data");
		SetValue($this->GetMyVariable("id_minMaxEnabledTemp"), false);
		SetValue($this->GetMyVariable("id_minMaxStart"), 0);
		SetValue($this->GetMyVariable("id_minMaxStop"), 0);
		SetValue($this->GetMyVariable("id_minMaxCnt"), 0);

		$childIDs = IPS_GetChildrenIDs($id_minMaxData);
		foreach($childIDs as $childId) {
			$objTyp = IPS_GetObject($childId)["ObjectType"];
			if($objTyp == 2) {
				SetValue($childId, 0);
				IPS_SetName($childId, IPS_GetObject($childId)["ObjectInfo"]);
			}
		}
	}

	public function ResetMinMaxWochenplan(string $source) {
		if ($this->logLevel >= LogLevel::INFO) { $this->AddLog(__FUNCTION__, 'RESET MinMaxWochenplan'); }
		$objIdMinMaxWochenplan = $this->GetMyVariable("id_MinMaxWochenplan");
		IPS_SetEventScheduleGroup ($objIdMinMaxWochenplan, 0, 0);
		IPS_SetEventScheduleGroup ($objIdMinMaxWochenplan, 1, 0);
		IPS_SetEventScheduleGroup ($objIdMinMaxWochenplan, 2, 0);
		IPS_SetEventScheduleGroup ($objIdMinMaxWochenplan, 3, 0);
		IPS_SetEventScheduleGroup ($objIdMinMaxWochenplan, 4, 0);
		IPS_SetEventScheduleGroup ($objIdMinMaxWochenplan, 5, 0);
		IPS_SetEventScheduleGroup ($objIdMinMaxWochenplan, 6, 0);
		IPS_SetEventScheduleGroup ($objIdMinMaxWochenplan, 0, 1);
		IPS_SetEventScheduleGroup ($objIdMinMaxWochenplan, 1, 2);
		IPS_SetEventScheduleGroup ($objIdMinMaxWochenplan, 2, 4);
		IPS_SetEventScheduleGroup ($objIdMinMaxWochenplan, 3, 8);
		IPS_SetEventScheduleGroup ($objIdMinMaxWochenplan, 4, 16);
		IPS_SetEventScheduleGroup ($objIdMinMaxWochenplan, 5, 32);
		IPS_SetEventScheduleGroup ($objIdMinMaxWochenplan, 6, 64);		
		IPS_SetEventActive($objIdMinMaxWochenplan, false);
	}

	public function DeleteLoggedData(string $Text) {

		if ($this->logLevel >= LogLevel::INFO) {
			$this->AddLog(__FUNCTION__, '  ..:: DELETE LOGGED DATA :: ..', 0);
		}
		$timerIntervalTemp = $this->GetTimerInterval("Timer_AutoUpdate_APRS");
		$this->SetTimerInterval("Timer_AutoUpdate_APRS", 0);
		if ($this->logLevel >= LogLevel::INFO) {
			$this->AddLog(__FUNCTION__, 'STOP "Timer_AutoUpdate_APRS" !', 0);
		}

		if ($this->logLevel >= LogLevel::DEBUG) {
			$this->AddLog(__FUNCTION__, sprintf("InstanceID: %s", $this->InstanceID), 0);
		}

		$archiveControlID = IPS_GetInstanceListByModuleID('{43192F0B-135B-4CE7-A0A7-1475603F3060}')[0];
		if ($this->logLevel >= LogLevel::DEBUG) {
			$this->AddLog(__FUNCTION__, sprintf("Archiv Conrol ID: %s", $archiveControlID), 0);
		}

		$childrenIDs = IPS_GetChildrenIDs($this->InstanceID);
		foreach ($childrenIDs as $childID) {
			if (IPS_GetObject($childID)["ObjectType"] == 2) {
				$loggingStatus = AC_GetLoggingStatus($archiveControlID, $childID);
				if ($loggingStatus) {
					if ($this->logLevel >= LogLevel::DEBUG) {
						$this->AddLog(__FUNCTION__, sprintf('Logging Status for Variable "[%s] %s" is TRUE', $childID, IPS_GetName($childID)), 0);
					}
					$result = AC_DeleteVariableData($archiveControlID, $childID, 0, time());
					if ($this->logLevel >= LogLevel::INFO) {
						$this->AddLog(__FUNCTION__, sprintf('%d Logged Values deleted for Variable "[%s] %s"', $result, $childID, IPS_GetName($childID)), 0);
					}
					$result = AC_ReAggregateVariable($archiveControlID, $childID);
					if ($this->logLevel >= LogLevel::INFO) {
						$this->AddLog(__FUNCTION__, sprintf('Start Reaggregation for Variable "[%s] %s" [result: %b]', $childID, IPS_GetName($childID), $result), 0);
					}
					IPS_Sleep(150);
				} else {
					if ($this->logLevel >= LogLevel::DEBUG) {
						$this->AddLog(__FUNCTION__, sprintf('Logging Status for Variable "[%s] %s" is FALSE', $childID, IPS_GetName($childID)), 0);
					}
				}
			} else {
				if ($this->logLevel >= LogLevel::DEBUG) {
					$this->AddLog(__FUNCTION__, sprintf('Object "[%s] %s" is no Variable', $childID, IPS_GetName($childID)), 0);
				}
			}
		}
		if ($this->logLevel >= LogLevel::INFO) {
			$this->AddLog(__FUNCTION__, sprintf('Restore Timer Interval for "Timer_AutoUpdate_APRS" to %d ms', $timerIntervalTemp), 0);
		}
		$this->SetTimerInterval("Timer_AutoUpdate_APRS", $timerIntervalTemp);
		if ($this->logLevel >= LogLevel::INFO) {
			$this->AddLog(__FUNCTION__, '  - - - :: LOGGED DATA DELETED :: - - - ', 0);
		}
	}

	protected function startsWith($haystack, $needle) {
		return strpos($haystack, $needle) === 0;
	}

	protected function String2Hex($string) {
		$hex = '';
		for ($i = 0; $i < strlen($string); $i++) {
			//$hex .= dechex(ord($string[$i]));
			$hex .= "0x" . sprintf("%02X", ord($string[$i])) . " ";
		}
		return trim($hex);
	}

	protected function ByteArr2HexStr($arr) {
		$hex_str = "";
		foreach ($arr as $byte) {
			$hex_str .= sprintf("%02X ", $byte);
		}
		return $hex_str;
	}


	public function WriteRawDataToLogFile(string $data) {

		$result = true;
		$filePath = IPS_GetLogDir() . "APRS/";
		$filePath .= date('Y-m-d', time()) . "/";
		$fileName = $filePath . $this->InstanceID . "_" .  date('Y-m-d_H', time()) . ".log";
		$now = DateTime::createFromFormat('U.u', microtime(true));
		$data = sprintf("%s :: %s\n", $now->format("Y-d-m H:i:s.u"), $data);

		if (!is_dir($filePath)) {
			$result = mkdir($filePath, 0777, true);
			if (!$result) {
				if ($this->logLevel >= LogLevel::WARN) {
					$this->AddLog(__FUNCTION__, sprintf("ERROR creating LogFile Path '%s' ", $filePath), 0);
				}
			}
		}
		if ($result) {
			$result = file_put_contents($fileName, $data, FILE_APPEND);
			if (!$result) {
				if ($this->logLevel >= LogLevel::WARN) {
					$this->AddLog(__FUNCTION__, sprintf("ERROR writing to file '%s' ", $fileName), 0);
				}
			}
		}
		return $result;
	}


	protected function AddLog($name, $daten, $format = 0, $ipsLogOutput = false) {
		$this->logCnt++;
		$logSender = "[" . __CLASS__ . "] - " . $name;
		if ($this->logLevel >= LogLevel::DEBUG) {
			$logSender = sprintf("%02d-T%2d [%s] - %s", $this->logCnt, $_IPS['THREAD'], __CLASS__, $name);
		}
		$this->SendDebug($logSender, $daten, $format);

		if ($ipsLogOutput or $this->enableIPSLogOutput) {
			if ($format == 0) {
				IPS_LogMessage($logSender, $daten);
			} else {
				IPS_LogMessage($logSender, $this->String2Hex($daten));
			}
		}
	}
}
