<?

trait COMMON {

	protected function RegisterProfiles() {

		if ( !IPS_VariableProfileExists('APRS_Distance.m') ) {
			IPS_CreateVariableProfile('APRS_Distance.m', 1 );
			//IPS_SetVariableProfileDigits('APRS_Distance.m', 0 );
			IPS_SetVariableProfileText('APRS_Distance.m', "", " m" );
			IPS_SetVariableProfileValues("APRS_Distance.m", 0, 8000, 50);
			IPS_SetVariableProfileIcon ("APRS_Distance.m", "Distance");
		} 


		if ( !IPS_VariableProfileExists('APRS_Distance.Meter') ) {
			IPS_CreateVariableProfile('APRS_Distance.Meter', 2 );
			//IPS_SetVariableProfileDigits('APRS_Distance.Meter', 0 );
			IPS_SetVariableProfileText('APRS_Distance.Meter', "", " m" );
			IPS_SetVariableProfileValues("APRS_Distance.Meter", 0, 8000, 50);
			IPS_SetVariableProfileIcon ("APRS_Distance.Meter", "Distance");
		} 

		if ( !IPS_VariableProfileExists('APRS_Distance.km') ) {
			IPS_CreateVariableProfile('APRS_Distance.km', 2 );
			IPS_SetVariableProfileDigits('APRS_Distance.km', 0 );
			IPS_SetVariableProfileText('APRS_Distance.km', "", " km" );
			IPS_SetVariableProfileValues("APRS_Distance.km", 0, 8000, 5);
			IPS_SetVariableProfileIcon ("APRS_Distance.km", "Distance");
		} 

		if ( !IPS_VariableProfileExists('APRS_Temp') ) {
			IPS_CreateVariableProfile('APRS_Temp', 2 );
			IPS_SetVariableProfileDigits('APRS_Temp', 1 );
			IPS_SetVariableProfileText('APRS_Temp', "", " °C" );
			IPS_SetVariableProfileValues("APRS_Temp", -300, 300, 5);
			IPS_SetVariableProfileIcon ("APRS_Temp", "Temperature");
		} 

		if ($this->logLevel >= LogLevel::DEBUG) { $this->AddLog(__FUNCTION__, "Variable Profiles registered"); }
	}

	protected function RegisterVariables() {

		$archivInstanzID = IPS_GetInstanceListByModuleID("{43192F0B-135B-4CE7-A0A7-1475603F3060}")[0];

		$parentRootId = IPS_GetParent($this->InstanceID);

		$categoryIdDataViewer = @IPS_GetObjectIDByIdent(self::CATEGORY_NAME_DataViewer, 0);
		if($categoryIdDataViewer !== false) { IPS_SetParent($categoryIdDataViewer, $parentRootId); }
		$categoryIdDataViewer = @IPS_GetObjectIDByIdent(self::CATEGORY_NAME_DataViewer, $parentRootId);
		if($categoryIdDataViewer === false) {
			$categoryIdDataViewer = IPS_CreateCategory();
			IPS_SetIdent($categoryIdDataViewer, self::CATEGORY_NAME_DataViewer);
			IPS_SetName($categoryIdDataViewer, self::CATEGORY_NAME_DataViewer);
			IPS_SetParent($categoryIdDataViewer, $parentRootId);
			IPS_SetPosition($categoryIdDataViewer, 100);
		}
		$varId = $this->RegisterVariableInteger("categoryIdDataViewer", "CategoryId DataViewer", "", 995);
		SetValueInteger($varId, $categoryIdDataViewer);
		IPS_SetHidden($varId, true);
		IPS_SetDisabled($varId, true);

		$categoryIdNotifications = @IPS_GetObjectIDByIdent(self::CATEGORY_NAME_Notifications, 0);
		if($categoryIdNotifications !== false) { IPS_SetParent($categoryIdNotifications, $parentRootId); }
		$categoryIdNotifications = @IPS_GetObjectIDByIdent(self::CATEGORY_NAME_Notifications, $parentRootId);
		if($categoryIdNotifications === false) {
			$categoryIdNotifications = IPS_CreateCategory();
			IPS_SetIdent($categoryIdNotifications, self::CATEGORY_NAME_Notifications);
			IPS_SetName($categoryIdNotifications, self::CATEGORY_NAME_Notifications);
			IPS_SetParent($categoryIdNotifications, $parentRootId);
			IPS_SetPosition($categoryIdNotifications, 110);
		}
		$varId = $this->RegisterVariableInteger("categoryIdNotifications", "CategoryId Notifications", "", 995);
		SetValueInteger($varId, $categoryIdNotifications);
		IPS_SetHidden($varId, true);
		IPS_SetDisabled($varId, true);		

		$categoryIdMinMax = @IPS_GetObjectIDByIdent(self::CATEGORY_NAME_MinMax, 0);
		if($categoryIdMinMax !== false) { IPS_SetParent($categoryIdMinMax, $parentRootId); } 
		$categoryIdMinMax = @IPS_GetObjectIDByIdent(self::CATEGORY_NAME_MinMax, $parentRootId);
		if($categoryIdMinMax === false) {
			$categoryIdMinMax = IPS_CreateCategory();
			IPS_SetIdent($categoryIdMinMax, self::CATEGORY_NAME_MinMax);
			IPS_SetName($categoryIdMinMax, self::CATEGORY_NAME_MinMax);
			IPS_SetParent($categoryIdMinMax, $parentRootId);
			IPS_SetPosition($categoryIdMinMax, 120);
		}
		$varId = $this->RegisterVariableInteger("categoryIdMinMax", "CategoryId MinMax", "", 995);
		SetValueInteger($varId, $categoryIdMinMax);
		IPS_SetHidden($varId, true);
		IPS_SetDisabled($varId, true);			

		$custActionScriptContent = '<? SetValue($_IPS["VARIABLE"], $_IPS["VALUE"]); ?>';
		$custActionScriptId = $this->RegisterScript("custAktionsskript", "Aktionsskript", $custActionScriptContent, 990);
		IPS_SetParent($custActionScriptId, $this->InstanceID);
		IPS_SetHidden($custActionScriptId, true);
		IPS_SetDisabled($custActionScriptId, true);


		$position = 100;
		$varId = $this->RegisterVariableBoolean("ParseAPRSData", "Parse APRS Data", "~Switch", $position);
		IPS_SetVariableCustomAction($varId, $custActionScriptId);

		$position++;
		$varId = $this->RegisterVariableString("aprsConnectionString", "APRS Connection String", "", $position);
		IPS_SetDisabled($varId, true);

		$position++;
		$varId = $this->RegisterVariableString("aprsNetbeacon", "APRS Netbeacon", "", $position);
		IPS_SetDisabled($varId, true);
		//$varId = $this->RegisterVariableString("aprsServer", "APRS Server", "", 12);
		//IPS_SetDisabled($varId, true);


		// ------------------------------------------------------------------------------------------------------------------------------------------
		// Counter Variables
		$position = 200;
		$varId = $this->RegisterVariableInteger("receiveCnt", "Receive Cnt", "", $position++);
		IPS_SetDisabled($varId, true);
		$varId = $this->RegisterVariableInteger("receivedBytes", "Received Bytes", "", $position++);
		IPS_SetDisabled($varId, true);
		$varId = $this->RegisterVariableInteger("receivedFrames", "Received Frames", "", $position++);
		IPS_SetDisabled($varId, true);
		$varId = $this->RegisterVariableFloat("processingTotalDuration", "Processing Total Duration [ms]", "", $position++);
		IPS_SetDisabled($varId, true);
		$varId = $this->RegisterVariableInteger("lastDataReceived", "Last Data Received", "~UnixTimestamp", $position++);
		IPS_SetDisabled($varId, true);
		$varId = $this->RegisterVariableInteger("instanzInactivCnt", "Instanz Inactiv Cnt", "", $position++);
		IPS_SetDisabled($varId, true);


		// ------------------------------------------------------------------------------------------------------------------------------------------
		// Reset Scripts
		$scriptContent = sprintf("<? APRS_ResetCounterVariables(%s, 'Script'); ?>", $this->InstanceID);
		$scriptId = $this->RegisterScript("resetCounterVariables", "Reset Counter Variables", $scriptContent, $position++);
		IPS_SetParent($scriptId, $this->InstanceID);
		IPS_SetHidden($scriptId, false);
		IPS_SetDisabled($scriptId, false);

		$position = 300;
		$varId = $this->RegisterVariableBoolean("saveToDB", "Save to MySQL DB", "~Switch", $position);
		IPS_SetVariableCustomAction($varId, $custActionScriptId);
		IPS_SetDisabled($varId, true);
		
		$position++;
		$varId = $this->RegisterVariableInteger("dbInsertId", "DB Insert ID", "", $position);
		IPS_SetDisabled($varId, true);

		$position++;
		$varId = $this->RegisterVariableFloat("dbInsertDuration", "DB Insert Duration [ms]", "", $position);
		IPS_SetDisabled($varId, true);

	
		// ------------------------------------------------------------------------------------------------------------------------------------------
		// LogFile

		$position = 350;
		$varId = $this->RegisterVariableBoolean("enableLogFile_1", "Log#1 - Write Received RawData", "~Switch", $position++);
		IPS_SetVariableCustomAction($varId, $custActionScriptId);
		
		$varId = $this->RegisterVariableBoolean("enableLogFile_2", "Log#2 - Write RawData Line to LogFiles", "~Switch", $position++);
		IPS_SetVariableCustomAction($varId, $custActionScriptId);
		
		$varId = $this->RegisterVariableBoolean("enableLogFile_3", "Log#3 - Write Part 1 RawData", "~Switch", $position++);
		IPS_SetVariableCustomAction($varId, $custActionScriptId);
		
		$varId = $this->RegisterVariableBoolean("enableLogFile_4", "Log#4 - Write Part 2 RawData ", "~Switch", $position++);
		IPS_SetVariableCustomAction($varId, $custActionScriptId);		
		
		$varId = $this->RegisterVariableBoolean("enableLogFile_5", "Log#5 - Write Part 2 as Array", "~Switch", $position++);
		IPS_SetVariableCustomAction($varId, $custActionScriptId);	

		$varId = $this->RegisterVariableBoolean("enableLogFile_DataViewer", "Log#6 - DataViewer", "~Switch", $position++);
		IPS_SetVariableCustomAction($varId, $custActionScriptId);	


		// ------------------------------------------------------------------------------------------------------------------------------------------
		// Data Viewer
		if(true) {
			$position = 400;

			$dummyIdDataViewerSettings = $this->CreateDummyInstance("DataViewerSettings", "Data Viewer Settings", $categoryIdDataViewer);

			$position++;
			$varId = $this->RegisterCustVariable("dataViewerEnabled", $dummyIdDataViewerSettings, "Enabled", VARIABLETYPE_BOOLEAN, $position, "~Switch", $custActionScriptId);
			$this->SetMyVariable("id_dataViewerEnabled", $varId);

			$position++;
			$varIdDistance = $this->RegisterCustVariable("dataViewer_Distance", $dummyIdDataViewerSettings, "Filter Distance to PG1ADW", VARIABLETYPE_FLOAT, $position, "APRS_Distance.km", $custActionScriptId);
			$this->SetMyVariable("id_dataViewer_Distance", $varIdDistance);
			//SetValue($varIdDistance, 4000);

			$position++;
			$varId = $this->RegisterCustVariable("dataViewer_Match1", $dummyIdDataViewerSettings, "Filter RawData 1 (wildcards '*' | '?')", VARIABLETYPE_STRING, $position, "", $custActionScriptId);
			$this->SetMyVariable("id_dataViewer_Match1", $varId);
			if(empty(GetValue($varId))) { SetValue($varId, ""); }

			$position++;
			$varId = $this->RegisterCustVariable("dataViewer_Match2", $dummyIdDataViewerSettings, "Filter RawData 2 (wildcards '*' | '?')", VARIABLETYPE_STRING, $position, "", $custActionScriptId);
			$this->SetMyVariable("id_dataViewer_Match2", $varId);
			if(empty(GetValue($varId))) { SetValue($varId, ""); }

			$position++;
			$varId = $this->RegisterCustVariable("dataViewer_Match3", $dummyIdDataViewerSettings, "Filter RawData 3 (wildcards '*' | '?')", VARIABLETYPE_STRING, $position, "", $custActionScriptId);
			$this->SetMyVariable("id_dataViewer_Match3", $varId);
			if(empty(GetValue($varId))) { SetValue($varId, ""); }			

			$position++;
			$varId = $this->RegisterCustVariable("dataViewer_StopOnNextMatch", $dummyIdDataViewerSettings, "Stop on next Filter Match", VARIABLETYPE_BOOLEAN, $position, "~Switch", $custActionScriptId);
			$this->SetMyVariable("id_dataViewer_StopOnNextMatch", $varId);

			$position++;
			$varId = $this->RegisterCustVariable("dataViewerCnt", $categoryIdDataViewer, "Match Cnt", VARIABLETYPE_INTEGER, $position, "", "");
			$this->SetMyVariable("id_dataViewerCnt", $varId);
			IPS_SetDisabled($varId, true);	

			$position++;
			$varId = $this->RegisterCustVariable("dataViewerCntMoMatch", $categoryIdDataViewer, "No Match Cnt", VARIABLETYPE_INTEGER, $position, "", "");
			$this->SetMyVariable("id_dataViewerCntMoMatch", $varId);
			IPS_SetDisabled($varId, true);	


			$position++;
			$scriptId = @IPS_GetObjectIDByIdent("resetDataViewerVariables", $categoryIdDataViewer);
			if($scriptId === false) {
				$scriptContent = sprintf("<? APRS_ResetDataViewerVariables(%s, 'Script'); ?>", $this->InstanceID);
				$scriptId = $this->RegisterScript("resetDataViewerVariables", "Reset Variables", $scriptContent, $position);
				IPS_SetParent($scriptId, $categoryIdDataViewer);
				IPS_SetHidden($scriptId, false);
				IPS_SetDisabled($scriptId, false);
			}
			$this->SetMyVariable("id_resetDataViewerVariables", $scriptId);

			$position++;
			$varId = $this->RegisterCustVariable("dataViewer", $categoryIdDataViewer, "Data Viewer", VARIABLETYPE_STRING, $position, "~TextBox", "");
			$this->SetMyVariable("id_dataViewer", $varId);
		
		}


		// ------------------------------------------------------------------------------------------------------------------------------------------
		// Notification 
		if(true) {
			$position = 510;
			$dummyIdNotify = $this->CreateDummyInstance("Notify", "Telegram Notification", $categoryIdNotifications);

			$position++;
			$varId = $this->RegisterCustVariable("notifyEnabled", $dummyIdNotify, "Enable Notify", VARIABLETYPE_BOOLEAN, $position, "~Switch", $custActionScriptId);
			$this->SetMyVariable("id_notifyEnabled", $varId);

			$position++;
			$varIdDistance = $this->RegisterCustVariable("notifyDistance", $dummyIdNotify, "Distance", VARIABLETYPE_FLOAT, $position, "APRS_Distance.km", $custActionScriptId);
			$this->SetMyVariable("id_notifyDistance", $varIdDistance);

			$position++;
			$varId = $this->RegisterCustVariable("notifyOzon", $dummyIdNotify, "Ozon", VARIABLETYPE_BOOLEAN, $position, "~Switch", $custActionScriptId);
			$this->SetMyVariable("id_notifyOzon", $varId);	

			$position++;
			$varId = $this->RegisterCustVariable("notifySondenTyp", $dummyIdNotify, "Sonden Typ", VARIABLETYPE_STRING, $position, "", $custActionScriptId);
			$this->SetMyVariable("id_notifySondenTyp", $varId);
			SetValue($varId, "");

			$position++;
			$varId = $this->RegisterCustVariable("notifyMatch1", $dummyIdNotify, "Filter RawData (wildcards '*' | '?')", VARIABLETYPE_STRING, $position, "", $custActionScriptId);
			$this->SetMyVariable("id_notifyMatch1", $varId);
			if(empty(GetValue($varId))) { SetValue($varId, ""); }

			$position++;
			$varId = $this->RegisterCustVariable("notifyMatch2", $dummyIdNotify, "Filter RawData (wildcards '*' | '?')", VARIABLETYPE_STRING, $position, "", $custActionScriptId);
			$this->SetMyVariable("id_notifyMatch2", $varId);
			if(empty(GetValue($varId))) { SetValue($varId, ""); }

			$position++;
			$varId = $this->RegisterCustVariable("notifyMatch3", $dummyIdNotify, "Filter RawData (wildcards '*' | '?')", VARIABLETYPE_STRING, $position, "", $custActionScriptId);
			$this->SetMyVariable("id_notifyMatch3", $varId);
			if(empty(GetValue($varId))) { SetValue($varId, ""); }			

			$position++;
			$varId = $this->RegisterCustVariable("notifyJsonStore", $dummyIdNotify, "JSON Store", VARIABLETYPE_STRING, $position, "", "");		
			$this->SetMyVariable("id_notifyJsonStore", $varId);
			//IPS_SetHidden($varId, true);
			IPS_SetDisabled($varId, true);

			$position++;
			$varId = $this->RegisterCustVariable("notifyJsonStoreCnt", $dummyIdNotify, "JSON Store Cnt", VARIABLETYPE_INTEGER, $position, "", "");
			$this->SetMyVariable("id_notifyJsonStoreCnt", $varId);
			IPS_SetDisabled($varId, true);

			$position++;
			$varId = $this->RegisterCustVariable("notifyCnt", $dummyIdNotify, "Messages Sent", VARIABLETYPE_INTEGER, $position, "", "");
			$this->SetMyVariable("id_notifyCnt", $varId);
			IPS_SetDisabled($varId, true);	

			$position++;
			$varId = $this->RegisterCustVariable("notifyMessage", $dummyIdNotify, "Last Message Sent", VARIABLETYPE_STRING, $position, "~HTMLBox", "");
			$this->SetMyVariable("id_notifyMessage", $varId);
			IPS_SetDisabled($varId, true);

			$position++;
			$scriptId = @IPS_GetObjectIDByIdent("resetNotifyVariables", $dummyIdNotify);
			if($scriptId === false) {
				$scriptContent = sprintf("<? APRS_ResetNotifyVariables(%s, 'Script'); ?>", $this->InstanceID);
				$scriptId = $this->RegisterScript("resetNotifyVariables", "Reset", $scriptContent, $position);
				IPS_SetParent($scriptId, $dummyIdNotify);
				IPS_SetHidden($scriptId, false);
				IPS_SetDisabled($scriptId, false);
			}
		}
		// ------------------------------------------------------------------------------------------------------------------------------------------
		// Notification PG1ADW20
		if(true) {
			$position = 520;
			$dummyIdNotifyPG1ADW20 = $this->CreateDummyInstance("NotifyPG1ADW", "Telegram Notification PG1ADW20", $categoryIdNotifications);

			$varId = $this->RegisterCustVariable("notifyPG1ADW", $dummyIdNotifyPG1ADW20, "Enable Notify", VARIABLETYPE_BOOLEAN, $position, "~Switch", $custActionScriptId);
			$this->SetMyVariable("id_notifyPG1ADW", $varId);

			$position++;
			$varIdDistance = $this->RegisterCustVariable("notifyPG1ADW_Distance", $dummyIdNotifyPG1ADW20, "Distance", VARIABLETYPE_FLOAT, $position, "APRS_Distance.km", $custActionScriptId);
			$this->SetMyVariable("id_notifyPG1ADW_Distance", $varIdDistance);
			SetValue($varIdDistance, 20);

			$position++;
			$varIdAltitude = $this->RegisterCustVariable("notifyPG1ADW_Altitude", $dummyIdNotifyPG1ADW20, "Altitude", VARIABLETYPE_INTEGER, $position, "APRS_Distance.m", $custActionScriptId);
			$this->SetMyVariable("id_notifyPG1ADW_Altitude", $varIdAltitude);
			SetValue($varIdAltitude, 4000);
	
			$position++;
			$varId = $this->RegisterCustVariable("notifyPG1ADW_JsonStore", $dummyIdNotifyPG1ADW20, "JSON Store", VARIABLETYPE_STRING, $position, "", "");		
			$this->SetMyVariable("id_notifyPG1ADW_JsonStore", $varId);
			IPS_SetDisabled($varId, true);

			$position++;
			$varId = $this->RegisterCustVariable("notifyPG1ADW_JsonStoreCnt", $dummyIdNotifyPG1ADW20, "JSON Store Cnt", VARIABLETYPE_INTEGER, $position, "", "");
			$this->SetMyVariable("id_notifyPG1ADW_JsonStoreCnt", $varId);
			IPS_SetDisabled($varId, true);

			$position++;
			$varId = $this->RegisterCustVariable("notifyPG1ADW_Cnt", $dummyIdNotifyPG1ADW20, "Messages Sent", VARIABLETYPE_INTEGER, $position, "", "");
			$this->SetMyVariable("id_notifyPG1ADW_Cnt", $varId);
			IPS_SetDisabled($varId, true);	

			$position++;
			$varId = $this->RegisterCustVariable("notifyPG1ADW_Message", $dummyIdNotifyPG1ADW20, "Last Message Sent", VARIABLETYPE_STRING, $position, "~HTMLBox", "");
			$this->SetMyVariable("id_notifyPG1ADW_Message", $varId);
			IPS_SetDisabled($varId, true);

			$position++;
			$scriptId = @IPS_GetObjectIDByIdent("resetNotifyPG1ADWVariables", $dummyIdNotifyPG1ADW20);
			if($scriptId === false) {
				$scriptContent = sprintf("<? APRS_ResetPG1ADWNotifyVariables(%s, 'Script'); ?>", $this->InstanceID);
				$scriptId = $this->RegisterScript("resetNotifyPG1ADWVariables", "Reset Variables", $scriptContent, $position);
				IPS_SetParent($scriptId, $dummyIdNotifyPG1ADW20);
				IPS_SetHidden($scriptId, false);
				IPS_SetDisabled($scriptId, false);
			}
		}

		// ------------------------------------------------------------------------------------------------------------------------------------------
		// Create MinMax Category Variabels
		if(true) {
			$position = 600;

			$dummyIdMinMaxSettings = $this->CreateDummyInstance("MinMaxSettings", "MinMax Settings", $categoryIdMinMax);
			IPS_SetPosition($dummyIdMinMaxSettings, $position);

			$position++;
			$varIdMinMaxEnabled  = $this->RegisterCustVariable("minMaxEnabled", $dummyIdMinMaxSettings, "Enable MinMax Tracking", VARIABLETYPE_BOOLEAN, $position, "~Switch", $custActionScriptId);	
			$this->SetMyVariable("id_minMaxEnabled", $varIdMinMaxEnabled);

			$position++;
			$varIdDistance = $this->RegisterCustVariable("minMax_Distance", $dummyIdMinMaxSettings, "Filter Distance to PG1ADW", VARIABLETYPE_FLOAT, $position, "APRS_Distance.km", $custActionScriptId);
			$this->SetMyVariable("id_minMax_Distance", $varIdDistance);

			$position++;
			$varId = $this->RegisterCustVariable("minMax_Match1", $dummyIdMinMaxSettings, "Filter RawData 1 (wildcards '*' | '?')", VARIABLETYPE_STRING, $position, "", $custActionScriptId);
			$this->SetMyVariable("id_minMax_Match1", $varId);
			if(empty(GetValue($varId))) { SetValue($varId, ""); }

			$position++;
			$varId = $this->RegisterCustVariable("minMax_Match2", $dummyIdMinMaxSettings, "Filter RawData 2 (wildcards '*' | '?')", VARIABLETYPE_STRING, $position, "", $custActionScriptId);
			$this->SetMyVariable("id_minMax_Match2", $varId);
			if(empty(GetValue($varId))) { SetValue($varId, ""); }
			
			$position++;
			$varId = $this->RegisterCustVariable("minMax_Match3", $dummyIdMinMaxSettings, "Filter RawData 3 (wildcards '*' | '?')", VARIABLETYPE_STRING, $position, "", $custActionScriptId);
			$this->SetMyVariable("id_minMax_Match3", $varId);
			if(empty(GetValue($varId))) { SetValue($varId, ""); }			

			$varIdMinMaxEnabledTemp = $this->RegisterCustVariable("minMaxEnabledTemp", $dummyIdMinMaxSettings, "Enable MinMax Tracking Temp", VARIABLETYPE_BOOLEAN, $position, "~Switch", $custActionScriptId);
			$this->SetMyVariable("id_minMaxEnabledTemp", $varIdMinMaxEnabledTemp);
			IPS_SetDisabled($varIdMinMaxEnabledTemp, true);
			IPS_SetHidden($varIdMinMaxEnabledTemp, true);

			$position++;
			$objIdMinMaxWochenplan = @IPS_GetObjectIDByIdent("MinMaxWochenplan", $dummyIdMinMaxSettings);
			if($objIdMinMaxWochenplan === false) {
				$objIdMinMaxWochenplan = IPS_CreateEvent(2);
				IPS_SetIdent($objIdMinMaxWochenplan,"MinMaxWochenplan");
				IPS_SetParent($objIdMinMaxWochenplan, $dummyIdMinMaxSettings);
				IPS_SetPosition($objIdMinMaxWochenplan, $position);
				IPS_SetName($objIdMinMaxWochenplan, "MinMax Wochenplan");

				IPS_SetEventScheduleActionEx($objIdMinMaxWochenplan, 0, "AUS", 0xEDEDED, "{3644F802-C152-464A-868A-242C2A3DEC5C}", ["VALUE" => false]);
				IPS_SetEventScheduleActionEx($objIdMinMaxWochenplan, 1, "AKTIV", 0x00FF11, "{3644F802-C152-464A-868A-242C2A3DEC5C}", ["VALUE" => true]);

				IPS_SetEventScheduleGroup ($objIdMinMaxWochenplan, 0, 1);
				IPS_SetEventScheduleGroup ($objIdMinMaxWochenplan, 1, 2);
				IPS_SetEventScheduleGroup ($objIdMinMaxWochenplan, 2, 4);
				IPS_SetEventScheduleGroup ($objIdMinMaxWochenplan, 3, 8);
				IPS_SetEventScheduleGroup ($objIdMinMaxWochenplan, 4, 16);
				IPS_SetEventScheduleGroup ($objIdMinMaxWochenplan, 5, 32);
				IPS_SetEventScheduleGroup ($objIdMinMaxWochenplan, 6, 64);
			}
			$this->SetMyVariable("id_MinMaxWochenplan", $objIdMinMaxWochenplan);

			$position++;
			$scriptId = @IPS_GetObjectIDByIdent("resetMinMaxWochenplan", $objIdMinMaxWochenplan);
			if($scriptId === false) {		
				$scriptContentResetWochenplant = sprintf("<? APRS_ResetMinMaxWochenplan(%s, 'Script'); ?>", $this->InstanceID);
				$scriptId = $this->RegisterScript("resetMinMaxWochenplan", "Reset MinMax Wochenplan", $scriptContentResetWochenplant, $position);
				IPS_SetParent($scriptId, $objIdMinMaxWochenplan);
				IPS_SetHidden($scriptId, false);
				IPS_SetDisabled($scriptId, false);
			}

			$position++;
			$varId = $this->RegisterCustVariable("minMaxStart", $categoryIdMinMax, "MinMax Start Zeitpunkt", VARIABLETYPE_INTEGER, $position, "~UnixTimestamp", "");	
			$this->SetMyVariable("id_minMaxStart", $varId);
			IPS_SetDisabled($varId, true);

			$position++;
			$varId = $this->RegisterCustVariable("minMaxStop", $categoryIdMinMax, "MinMax Stop Zeitpunkt", VARIABLETYPE_INTEGER, $position, "~UnixTimestamp", "");	
			$this->SetMyVariable("id_minMaxStop", $varId);
			IPS_SetDisabled($varId, true);

			$position++;
			$varId = $this->RegisterCustVariable("minMaxCnt", $categoryIdMinMax, "Update Cnt", VARIABLETYPE_INTEGER, $position, "", "");
			$this->SetMyVariable("id_minMaxCnt", $varId);
			IPS_SetDisabled($varId, true);	

			$position++;
			$scriptId = @IPS_GetObjectIDByIdent("resetMinMaxVariables", $categoryIdMinMax);
			if($scriptId === false) {
				$scriptContent = sprintf("<? APRS_ResetMinMaxVariables(%s, 'Script'); ?>", $this->InstanceID);
				$scriptId = $this->RegisterScript("resetMinMaxVariables", "Reset MinMax Variables", $scriptContent, $position);
				IPS_SetParent($scriptId, $categoryIdMinMax);
				IPS_SetHidden($scriptId, false);
				IPS_SetDisabled($scriptId, false);
			}
			$this->SetMyVariable("id_resetMinMaxVariables", $scriptId);

			$position++;
			$varId = $this->CreateDummyInstance(self::DUMMY_NAME_MinMax, "MinMax Data", $categoryIdMinMax, $position);
			$this->SetMyVariable("id_minMaxData", $varId);

		}

		// ------------------------------------------------------------------------------------------------------------------------------------------
		// TelegramBot

		$scriptFileSource = sprintf("<?php echo 'InstanceID is %s' ?>", $this->InstanceID);
		$parentDirectory = dirname(__FILE__);
		$scriptFile = $parentDirectory . '/telegramBot.txt';
		$file = @file_get_contents($scriptFile);
		if($file === false) {
			if($this->logLevel >= LogLevel::WARN) { $this->AddLog(__FUNCTION__, sprintf("WARN: Register minimal Script. File '%s' NOT exists", $scriptFile)); }
			$scriptFileSource = sprintf('<?php IPS_LogMessage("APRS-Module", "WARN: TelegramBot Script File not found") ?>', $this->InstanceID);				
		} else {
			if($this->logLevel >= LogLevel::INFO) { $this->AddLog(__FUNCTION__, sprintf("Register Script > Source File Template '%s'", $scriptFile)); }
			$scriptFileSource = str_replace("%%INSTANCE_ID%%", strval($this->InstanceID), $file);			
		}
		$objId = $this->RegisterScript("telegramBot", "TelegramBot", $scriptFileSource, 900);
		IPS_SetHidden($objId, false);
		IPS_SetDisabled($objId, true);

		IPS_ApplyChanges($archivInstanzID);

		if ($this->logLevel >= LogLevel::INFO) { $this->AddLog(__FUNCTION__, "Variables registered"); }

		$bufferArr =  $this->GetBufferList();
		foreach($bufferArr as $key => $value) {
			if ($this->logLevel >= LogLevel::INFO) { $this->AddLog(__FUNCTION__, sprintf(" Buffer [%s] '%s': %s", $key, $value, $this->GetMyVariable($value))); }
		}
	
	}

	protected function CreateDummyInstance(string $ident, string $name, int $parentId, int $position=0) {
        //$instanceId = @IPS_GetInstanceIDByName($name, $parentId);
		$instanceId = @IPS_GetObjectIDByIdent($ident, $parentId);
        if($instanceId === false) {
           $instanceId = IPS_CreateInstance("{485D0419-BE97-4548-AA9C-C083EB82E61E}");
		   IPS_SetIdent($instanceId, $ident);
		   IPS_SetParent($instanceId, $parentId);
           IPS_SetName($instanceId, $name);
           IPS_SetPosition($instanceId, $position);
        }
        return $instanceId;
     }
 
     protected function CreateLink(string $linkName, int $linkTo, int $position, int $parentId) {
         $linkID = @IPS_GetLinkIDByName($linkName, $parentId);
         if ($linkID === false) {
             $linkID = IPS_CreateLink();
             IPS_SetParent($linkID, $parentId);
             IPS_SetName($linkID, $linkName);
             IPS_SetPosition($linkID, $position);
             IPS_SetLinkTargetID($linkID, $linkTo);
         }
         return $linkID;
     }
 
 
     private function RegisterCustVariable($ident, $parentId, $name, $type, $position=0, $profile="", $action="" ) {
 
         $varId = @IPS_GetObjectIDByIdent($ident, $parentId);
         if($varId === false) {
             $varId = IPS_CreateVariable($type);
             IPS_SetParent($varId, $parentId);
             IPS_SetIdent($varId, $ident);
             IPS_SetName($varId, $name);
             IPS_SetPosition($varId, $position);
             IPS_SetVariableCustomProfile($varId, $profile);
             if($action != "") {
                 IPS_SetVariableCustomAction($varId, $action);
             }
         }
         return $varId;
     }
 
 
     private function SetMyVariable(string $varName, $value) {
         return $this->SetBuffer($varName, $value);
     }
 
     private function GetMyVariable(string $varName) {
         return $this->GetBuffer($varName);
     }

     protected function GetMinMaxVarId(string $identName, string $varName) {

		//$categoryIdMinMax = GetValueInteger($this->GetIDForIdent("categoryIdMinMax"));
		//$dummyIdMinMax = $this->CreateDummyInstance("MinMax", $categoryIdMinMax);
		
		$varId = false;
		$minMaxData =  $this->GetMyVariable("id_minMaxData");

		//$this->AddLog(__FUNCTION__, sprintf("id_minMaxData: %s", $minMaxData));
		$varId = @IPS_GetObjectIDByIdent($identName, $minMaxData);
		if ($varId === false) {
			$varId = IPS_CreateVariable(2);     //0 - Boolean | 1-Integer | 2 - Float | 3 - String
			IPS_SetParent($varId, $minMaxData);
			IPS_SetIdent($varId, $identName);
			IPS_SetPosition($varId, 650);
			IPS_SetName($varId, $varName);
			
			if(strpos($identName, "altitude") !== false) {
				IPS_SetVariableCustomProfile($varId, "APRS_Distance.Meter");
			} else if(strpos($identName, "overGround") !== false) {
				IPS_SetVariableCustomProfile($varId, "APRS_Distance.Meter");
			} else if(strpos($identName, "distance") !== false) {
				IPS_SetVariableCustomProfile($varId, "APRS_Distance.km");
			} else if(strpos($identName, "speed") !== false) {
				IPS_SetVariableCustomProfile($varId, "~WindSpeed.kmh");
			} else if(strpos($identName, "clb") !== false) {
				IPS_SetVariableCustomProfile($varId, "~WindSpeed.ms");
			} else if(strpos($identName, "pressure") !== false) {
				IPS_SetVariableCustomProfile($varId, "~AirPressure.F");
			} else if(strpos($identName, "temp") !== false) {
				IPS_SetVariableCustomProfile($varId, "APRS_Temp");
			} else if(strpos($identName, "humidity") !== false) {
				IPS_SetVariableCustomProfile($varId, "~Humidity.F");
			} else if(strpos($identName, "batt") !== false) {
				IPS_SetVariableCustomProfile($varId, "~Volt");
			} else if(strpos($identName, "o3") !== false) {
				//IPS_SetVariableCustomProfile($varId, "");											
				//IPS_LogMessage("APRS_Modul MinMax", "no Profile for '".$identName."'");
			} else {
				IPS_LogMessage("APRS_Modul MinMax", "no Profile for '".$identName."'");
				//IPS_SetVariableCustomProfile($varId, "");
			}
			IPS_SetInfo($varId, $varName);
			IPS_SetDisabled($varId, true);
		}

		return $varId;
	}

}

?>