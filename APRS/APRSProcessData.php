<?

trait APRSProcessData {

	protected function ProcessData($rawData) {

		$returnVal = 0;

		SetValue($this->GetIDForIdent("receivedBytes"), strlen($rawData));
		$rawData = rtrim($rawData, " \n\r\t\v\0");
		$rawDataArr = explode(PHP_EOL, $rawData);
		SetValue($this->GetIDForIdent("receivedFrames"), count($rawDataArr));

		$enableLogFile_1 = GetValue($this->GetIDForIdent("enableLogFile_1"));
		if ($enableLogFile_1) {
			$this->WriteRawDataToLogFile($rawData);
		}

        // ------------------------------------------------------------------------------------------------------
        // parse APRS Data Line/Frame
		$parseAPRSData = GetValueBoolean($this->GetIDForIdent("ParseAPRSData"));
		if($parseAPRSData) {
			$rawDataLines = 0;
			foreach ($rawDataArr as $rawData) {

				if ($this->startsWith($rawData, "#")) {
					if ($this->logLevel >= LogLevel::INFO) {
						$this->AddLog(__FUNCTION__, "RawData '#': " . $rawData);
					}
				} else {

					$rawDataLines++;
					$enableLogFile_2 = GetValue($this->GetIDForIdent("enableLogFile_2"));
					if ($enableLogFile_2) {
						$this->WriteRawDataToLogFile(sprintf("#%d :: %s", $rawDataLines, $rawData));
					}

                    // -------------------- parse APRS Data to Array
					$dataArr = $this->ParseRawDataNEW($rawData);
                    
                    $distPG1ADW = $dataArr["distPG1ADW"];

                    // ++++++++++++ IPS Data View ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
                    if(true) {
                        $dataViewerEnabled = GetValueBoolean($this->GetMyVariable("id_dataViewerEnabled"));
                        $varIdDataViewer = $this->GetMyVariable("id_dataViewer");				
                        if ($dataViewerEnabled) {

                            $filterResult = $this->FilterAPRSData("Data Viewer", "id_dataViewer_Distance", "id_dataViewer_Match", $distPG1ADW, $rawData);
                            $filterTxt = $filterResult["FilterText"];
                            if($filterResult["FilterPassed"]) {
                                SetValue($varIdDataViewer, print_r($dataArr, true));

                                $id_dataViewerCnt = $this->GetMyVariable("id_dataViewerCnt");
                                $dataViewerCnt = GetValue($id_dataViewerCnt); 
                                SetValue($id_dataViewerCnt, $dataViewerCnt + 1); 

                                $dataViewer_StopOnNextMatch = GetValue($this->GetMyVariable("id_dataViewer_StopOnNextMatch"));   
                                if($dataViewer_StopOnNextMatch) {
                                    SetValueBoolean($this->GetMyVariable("id_dataViewerEnabled"), false);
                                    $filterTxt .= sprintf(" | AUTO STOPPED @%s", date('d.m.Y H:i:s',time()) );
                                } else {
                                    $filterTxt .= " @" . date('d.m.Y H:i:s',time()); 
                                }
                                IPS_SetName($varIdDataViewer, $filterTxt); 
                            }

                        } else {

                            $dataViewer_StopOnNextMatch = GetValue($this->GetMyVariable("id_dataViewer_StopOnNextMatch"));   
                            if(!$dataViewer_StopOnNextMatch) {
                   
                                $lastUpdated = IPS_GetVariable($varIdDataViewer)["VariableUpdated"];
                                $lastUpdateSec = time() - round($lastUpdated);
                                if($lastUpdateSec >= 600) {
                                    SetValue($varIdDataViewer, "- - -");
                                    IPS_SetName($varIdDataViewer, "Data Viewer");
                                } else {
                                    IPS_SetName($varIdDataViewer, sprintf("Data Viewer [%d]", 600 - $lastUpdateSec));
                                }

                            } 
                        }
                    }
                    // -------------------------------------------------------------------------------------------


                    // ++++++++++++ Save to DB +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
					if(true) { 
                        $saveToDB = GetValue($this->GetIDForIdent("saveToDB"));
                        if ($saveToDB) {
                            if ($this->logLevel >= LogLevel::DEBUG) {
                                $this->AddLog(__FUNCTION__, "SAVE to DB ...", 0);
                            }
                            $returnVal += $this->SaveToDB($dataArr);
                        } else {
                            SetValue($this->GetIDForIdent("dbInsertId"), 0);
                            SetValue($this->GetIDForIdent("dbInsertDuration"), 0);
                        }
                        // -------------------------------------------------------------------------------------------
                    }


                    // ++++++++++++ MinMax +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
                    if(true) { 
                        $varIdMinMaxEnabled = $this->GetMyVariable("id_minMaxEnabled");
                        $varIdMinMaxEnabledTemp = $this->GetMyVariable("id_minMaxEnabledTemp");
                        $minMaxEnabled = GetValueBoolean($varIdMinMaxEnabled);
                        $minMaxEnabledTemp = GetValueBoolean($varIdMinMaxEnabledTemp);
                        if($minMaxEnabled != $minMaxEnabledTemp) {
                            if($minMaxEnabled) {
                                SetValueInteger($this->GetMyVariable("id_minMaxStart"), time());
                                SetValueInteger($this->GetMyVariable("id_minMaxStop"), 0);
                                //$this->ResetMinMaxVariables("OnOff");
                            } else {
                                SetValueInteger($this->GetMyVariable("id_minMaxStop"), time());							
                            }
                            SetValueBoolean($varIdMinMaxEnabledTemp, $minMaxEnabled);
                        }

                        if($minMaxEnabled) {
                      
                            $filterResult = $this->FilterAPRSData("MinMax Data", "id_minMax_Distance", "id_minMax_Match", $distPG1ADW, $rawData);
                            if($filterResult["FilterPassed"]) {

                                $minMaxData =  $this->GetMyVariable("id_minMaxData");
                                IPS_SetName($minMaxData, $filterResult["FilterText"] . " @" . date('d.m.Y H:i:s',time())); 

                                if (true) {

                                    $objName = $dataArr["objName"];
                                    $timeStamp = $dataArr["timeStamp"];
        
                                    if(is_null($objName)) {
                                        if($this->logLevel >= LogLevel::INFO) { $this->AddLog(__FUNCTION__, "WARN: 'objName' is NULL"); }
                                    } else {

                                        $this->CheckMinValue("distanceMin", "Distance PG1ADW MIN", $distPG1ADW, $objName, $timeStamp);
                                        $this->CheckMaxValue("distanceMax", "Distance PG1ADW MAX", $distPG1ADW, $objName, $timeStamp);								

                                        $altitude = $dataArr["altitude"];
                                        $this->CheckMinValue("altitudeMin", "Altitude MIN", $altitude, $objName, $timeStamp);
                                        $this->CheckMaxValue("altitudeMax", "Altitude MAX", $altitude, $objName, $timeStamp);

                                        $speed = $dataArr["speed"];
                                        $this->CheckMinValue("speedMin", "Speed MIN", $speed, $objName, $timeStamp);
                                        $this->CheckMaxValue("speedMax", "Speed MAX", $speed, $objName, $timeStamp);

                                        $clb = $dataArr["Clb"];
                                        if($clb > 0) {
                                            $this->CheckMinValue("clbUpMin", "Clb Up MIN", $clb, $objName, $timeStamp);
                                            $this->CheckMaxValue("clbUpMax", "Clb Up MAX", $clb, $objName, $timeStamp);
                                        } else {
                                            $clb = abs($clb);
                                            $this->CheckMinValue("clbDownMin", "Clb Down MIN", $clb, $objName, $timeStamp);
                                            $this->CheckMaxValue("clbDownMax", "Clb Down MAX", $clb, $objName, $timeStamp);
                                        }

                                        $pressure = $dataArr["p"];
                                        $this->CheckMinValue("pressureMin", "Pressure MIN", $pressure, $objName, $timeStamp);
                                        $this->CheckMaxValue("pressureMax", "Pressure MAX", $pressure, $objName, $timeStamp);		
                                        
                                        $temperature = $dataArr["t"];
                                        $this->CheckMinValue("tempMin", "Temperatur MIN", $temperature, $objName, $timeStamp);
                                        $this->CheckMaxValue("tempMax", "Temperatur MAX", $temperature, $objName, $timeStamp);		
                                        
                                        $humidity = $dataArr["h"];
                                        $this->CheckMinValue("humidityMin", "Humidity MIN", $humidity, $objName, $timeStamp);
                                        $this->CheckMaxValue("humidityMax", "Humidity M", $humidity, $objName, $timeStamp);		
                                        
                                        $o3 = $dataArr["o3"];
                                        $this->CheckMinValue("o3Min", "o3 MIN", $o3, $objName, $timeStamp);
                                        $this->CheckMaxValue("o3Max", "o3 MAX", $o3, $objName, $timeStamp);	
                                        
                                        $overGround = $dataArr["OG"];
                                        $this->CheckMinValue("overGroundMin", "OverGround MIN", $overGround, $objName, $timeStamp);
                                        $this->CheckMaxValue("overGroundMax", "OverGround MAX", $overGround, $objName, $timeStamp);									
                                    }
                                }
                              
                            }
                        }
                    }
                    // -------------------------------------------------------------------------------------------

					// ++++++++++++ Notify +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
                    if(true) { 
                        $notifyEnabled = GetValue($this->GetMyVariable("id_notifyEnabled"));
                        if ($notifyEnabled) {

                             $doNotifyFor = [];
       
                            $notifyDistance = GetValue($this->GetMyVariable("id_notifyDistance"));
                            if(($notifyDistance == 0) or (is_null($distPG1ADW)) or ($distPG1ADW < $notifyDistance)) { 

                                $notifyOzon = GetValueBoolean($this->GetMyVariable("id_notifyOzon"));
                                if($notifyOzon) {
                                    $o3Value = $dataArr["o3"];
                                    if (!is_null($o3Value)) {
                                        $doNotifyFor[] = "Ozon";
                                    }
                                }

                                $notifySondenTyp =  GetValue($this->GetMyVariable("id_notifySondenTyp"));
                                if(empty($notifySondenTyp) or ($notifySondenTyp == "disabled")) {
                                    // no match filter > do nothing
                                } else {
                                    $typeValue = $dataArr["Type"];
                                    if(!empty($typeValue)) {
                                        $pos = strpos($notifySondenTyp, $typeValue);
                                        if ($pos !== false) {
                                            $doNotifyFor[] = $typeValue;
                                        }
                                    } else {
                                        if($this->logLevel >= LogLevel::WARN) { $this->AddLog(__FUNCTION__, "kein SondenTyp vorhanden: " . $rawData); }
                                    }
                                }

                                $notifyMatch =  GetValue($this->GetMyVariable("id_notifyMatch"));
                                if(empty($notifyMatch) or ($notifyMatch == "disabled")) {
                                    // no match filter > do nothing
                                } else {
                                    if(fnmatch($notifyMatch, $rawData, FNM_NOESCAPE)) {
                                        $doNotifyFor[] = $notifyMatch;
                                    }                                    
                                }
            
                            } else {
                                if($this->logLevel >= LogLevel::TRACE) { $this->AddLog(__FUNCTION__, "Notify Distance Filter not match"); }
                            }


                           
                           //$doNotifyForCnt =  count($doNotifyFor);
                           //if( $doNotifyForCnt > 0) {
                           //     IPS_LogMessage(__FUNCTION__, print_r($doNotifyFor, true));
                           //}

                            foreach($doNotifyFor as $notifyFor) {

                                $callSign = $dataArr["callSign"];
                                $objName = $dataArr["objName"];
                                $type = $dataArr["Type"];
                                $frequenz = $dataArr["MHz"];
                                $altitude = $dataArr["altitude"];
                                $speed = $dataArr["speed"];
                                $clb = $dataArr["Clb"];
                                $rawData = $dataArr["rawData"];

                                $dataStoreKey = $objName . "_" . $notifyFor;

                                $varIdNotifyJsonStore =  $this->GetMyVariable("id_notifyJsonStore");
                                $jsonDataStore =  GetValue($varIdNotifyJsonStore);
                                $jsonDataStoreArr = json_decode($jsonDataStore, true);
                                if ($jsonDataStoreArr === null) { $jsonDataStoreArr = array(); }

                                if (!array_key_exists($dataStoreKey, $jsonDataStoreArr)) {

                                    if (count($jsonDataStoreArr) > 99) { $jsonDataStoreArr = array(); }

                                    $dataStoreEntry = array();
                                    $dataStoreEntry["objName"] = $objName;
                                    $dataStoreEntry["Trigger"] = $notifyFor;
                                    $dataStoreEntry["TimeStamp"] = date('d.m.Y H:i:s', time());
                                    $jsonDataStoreArr[$dataStoreKey] = $dataStoreEntry;

                                    $linkRadioSondy =  sprintf("https://radiosondy.info/sonde.php?sondenumber=%s", $objName);
                                    $notifyMsg = sprintf("Erster Datensatz der Sonde <b>'%s'</b> wurde von '%s' empfangen.\n", $objName, $callSign);
                                    $notifyMsg .= sprintf("\nNotification Trigger: <b>'%s'</b>\n", $notifyFor);
                                    $notifyMsg .= sprintf("\nEmpfänger: %s \nNummer: %s \nType: %s \nFrequenz: %s MHz \nAltitude: %s m \nSpeed: %s km/h \nClb: %s m/s ", $callSign, $objName, $type, $frequenz, $altitude, $speed, $clb);
                                    $distPG1ADW = $dataArr["distPG1ADW"];
                                    if (is_null($distPG1ADW)) {
                                        $notifyMsg .= sprintf("\nDistance to PG1ADW: %s km", "NULL");
                                    } else {
                                        $notifyMsg .= sprintf("\nDistance to PG1ADW is: '%s'", $distPG1ADW);
                                    }
                                    $notifyMsg .= sprintf("\n\n<i>%s</i>\n%s ", $rawData, $linkRadioSondy);

                                    SetValue($varIdNotifyJsonStore, json_encode($jsonDataStoreArr, true));
                                    SetValue($this->GetMyVariable("id_notifyJsonStoreCnt"), count($jsonDataStoreArr));
                                    SetValue($this->GetMyVariable("id_notifyMessage"), $notifyMsg);
                                    $id_notifyCnt = $this->GetMyVariable("id_notifyCnt");
                                    SetValue($id_notifyCnt, GetValue($id_notifyCnt)+1);

                                    if($this->logLevel >= LogLevel::INFO) { $this->AddLog(__FUNCTION__, sprintf("Notify for '%s'\r\n%s ", $notifyFor, $notifyMsg)); } 

                                    $this->SendTelegramMessage($notifyMsg, "");
                                }
                            }
                        }
                    }
					// - - - - - - - END NOTIFY  - - - - - - - - - -    

                    // ++++++++++++ NOTIFY PG1ADW +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
                    if(true) {                     
                        $notifyPG1ADW = GetValue($this->GetMyVariable("id_notifyPG1ADW"));
                        if ($notifyPG1ADW) {

                            $dataStoreKey = "";
                            $objName = $dataArr["objName"];
                            $altitude = $dataArr["altitude"];
                            $distPG1ADW = $dataArr["distPG1ADW"];
                            if (!is_null($distPG1ADW)) {

                                $notifyPG1ADW_Distance = GetValue($this->GetMyVariable("id_notifyPG1ADW_Distance"));
                                if ($distPG1ADW < $notifyPG1ADW_Distance) {
                                    $dataStoreKey = $objName . "_veryNear";
                                    $notifyMsg = sprintf("Datensatz der Sonde <b>'%s'</b>\nganz in der Nähe wurde empfangen.", $objName);

                                    $clb = $dataArr["Clb"];
                                    if (!is_null($clb)) {
                                        if ($clb < 0) {

                                            if (!is_null($distPG1ADW)) {
                                                $notifyPG1ADW_Altitude = GetValue($this->GetMyVariable("id_notifyPG1ADW_Altitude"));
                                                if ($altitude < $notifyPG1ADW_Altitude) {
                                                    $altitudeKey = round($altitude / 200) * 200;
                                                    $dataStoreKey = sprintf("%s_pnLanding_%s", $objName, $altitudeKey);
                                                    $notifyMsg = sprintf("!!! Possible Nearby LANDING !!!\nDatensatz der Sonde <b>'%s'</b> \n in %s km Entfernung und\n %s m Höhe wurde empfangen.", $objName, $distPG1ADW, $altitude);
                                                }
                                            }
                                        }
                                    }
                                }
                            }


                            if ($dataStoreKey != "") {

                                $callSign = $dataArr["callSign"];
                                $objName = $dataArr["objName"];
                                $type = $dataArr["Type"];
                                $frequenz = $dataArr["MHz"];
                                $altitude = $dataArr["altitude"];
                                $speed = $dataArr["speed"];
                                $clb = $dataArr["Clb"];
                                $rawData = $dataArr["rawData"];

                                $jsonDataStore =  GetValue($this->GetMyVariable("notifyPG1ADW_JsonStore"));
                                $jsonDataStoreArr = json_decode($jsonDataStore, true);
                                if ($jsonDataStoreArr === null) {
                                    $jsonDataStoreArr = array();
                                }

                                if (!array_key_exists($dataStoreKey, $jsonDataStoreArr)) {

                                    if (count($jsonDataStoreArr) > 99) { $jsonDataStoreArr = array(); }

                                    $dataStoreEntry = array();
                                    $dataStoreEntry["objName"] = $objName;
                                    $dataStoreEntry["TimeStamp"] = date('d.m.Y H:i:s', time());
                                    $jsonDataStoreArr[$dataStoreKey] = $dataStoreEntry;
            
                                    $linkRadioSondy =  sprintf("https://radiosondy.info/sonde.php?sondenumber=%s", $objName);
                                    $notifyMsg .= sprintf("\n\nEmpfänger: %s \nNummer: %s \nType: %s \nFrequenz: %s MHz \nAltitude: %s m \nSpeed: %s km/h \nClb: %s m/s ", $callSign, $objName, $type, $frequenz, $altitude, $speed, $clb);
                                    $notifyMsg .= sprintf("\nDistance to PG1ADW: %s km\n\n<i>%s</i>\n%s ", $dataArr["distPG1ADW"], $rawData, $linkRadioSondy);

                                    SetValue($jsonDataStore, json_encode($jsonDataStoreArr, true));
                                    SetValue($this->GetMyVariable("id_notifyPG1ADW_JsonStore"), $notifyMsg);
                                    SetValue($this->GetMyVariable("id_notifyPG1ADW_JsonStoreCnt"), count($jsonDataStoreArr));

                                    SetValue($this->GetMyVariable("id_notifyPG1ADW_Message"), $notifyMsg);
                                    $id_notifyPG1ADW_Cnt = $this->GetMyVariable("id_notifyPG1ADW_Cnt");
                                    SetValue($id_notifyPG1ADW_Cnt, GetValue($id_notifyPG1ADW_Cnt)+1);

                                    $this->SendTelegramMessage($notifyMsg, "PG1ADW20");
                                }
                            }
                        }
                    }
					// - - - - - - - END NOTIFY PG1ADW- - - - - - - - - -             

				}
			}
			if ($this->logLevel >= LogLevel::TRACE) {
				$this->AddLog(__FUNCTION__, sprintf("RawDataLines: %s | %s records inserted in DB", $rawDataLines, $returnVal), 0);
			}
		}
		return $returnVal;
	}

    protected function FilterAPRSData($filterName, $id_filterDistance, $id_filterMatch, $distPG1ADW, $rawData) {

        $filterPassed = false; 
        $filterTxt = $filterName . " { ";

        $filterDistance = GetValueFloat($this->GetMyVariable($id_filterDistance)); 
        if($filterDistance > 0) {                               
            if (is_null($distPG1ADW)) {
                $filterPassed = true;
                $filterTxt .= "'distPG1ADW' is NULL";
            } else {
                if ($distPG1ADW <= $filterDistance) {
                    $filterPassed = true;
                    $filterTxt .= sprintf("distPG1ADW: %s km", $distPG1ADW);
                } else {
                    if($this->logLevel >= LogLevel::TEST) { $this->AddLog(__FUNCTION__, sprintf("%s Distance Filter '%s < %s' ", $filterName, $distPG1ADW, $filterDistance)); } 
                }
            }
        } else {
            $filterPassed = true;
            $filterTxt .= sprintf("Distance Filter disabled", $distPG1ADW);
        }

        if($filterPassed) {
            $filterMatch = GetValue($this->GetMyVariable($id_filterMatch));                              
            if(empty($filterMatch) or ($filterMatch == "*")) {
                $filterPassed = true;
                $filterTxt .= " | match Filter disabled }";
            } else {
                if(fnmatch($filterMatch, $rawData, FNM_NOESCAPE)) {
                    $filterPassed = true;
                    $filterTxt .= sprintf(" | Filter '%s' matched}", $filterMatch);
                } else {
                    $filterPassed = false;
                }
            }
        } else {
            $filterTxt .= " }";
        }

        $filterResult = [];
        $filterResult["FilterText"] = $filterTxt;
        $filterResult["FilterPassed"] = $filterPassed;

        if($this->logLevel >= LogLevel::DEBUG) { $this->AddLog(__FUNCTION__, sprintf("FilterName '%s':  FilterPassed: %s | FilterText: %s", $filterName, $filterResult["FilterPassed"] , $filterResult["FilterText"])); } 
        return $filterResult;
    }

	protected function CheckMinValue(string $identId, string $varName, $value, string $objName, int $timeStamp) {
		$varId = $this->GetMinMaxVarId($identId, $varName);
        if($varId === false) {
            $id_minMaxCnt = $this->GetMyVariable("id_minMaxCnt");
            SetValue($id_minMaxCnt, GetValue($id_minMaxCnt) - 1); 
        } else {
            $valueMin = GetValue($varId);
            if((!is_null($valueMin)) AND ($value > 0)) {
                if(($value < $valueMin) or ($valueMin == 0)) {
                    SetValue($varId, $value);
                    IPS_SetName($varId, sprintf("%s '%s' @%s", $varName, $objName, date('d.m.Y H:i:s', $timeStamp)));
                    $id_minMaxCnt = $this->GetMyVariable("id_minMaxCnt");
                    SetValue($id_minMaxCnt, GetValue($id_minMaxCnt) + 1); 
                }
            }
        }
	}

	protected function CheckMaxValue(string $identId, string $varName, $value, string $objName, int $timeStamp) {
		$varId = $this->GetMinMaxVarId($identId, $varName);
        if($varId === false) {
            $id_minMaxCnt = $this->GetMyVariable("id_minMaxCnt");
            SetValue($id_minMaxCnt, GetValue($id_minMaxCnt) - 1); 
        } else {
            $valueMax = GetValue($varId);
            if(!is_null($valueMax)) {
                if($value > $valueMax) {
                    SetValue($varId, $value);
                    $varName = IPS_GetObject($varId)["ObjectInfo"];
                    IPS_SetName($varId, sprintf("%s '%s' @%s", $varName, $objName, date('d.m.Y H:i:s', $timeStamp)));
                    $id_minMaxCnt = $this->GetMyVariable("id_minMaxCnt");
                    SetValue($id_minMaxCnt, GetValue($id_minMaxCnt) + 1);                 
                }
            }
        }        
	}

	public function SendTelegramMessage(string $notifyMsg, string $sender) {
		$telegramBotToken = $this->ReadPropertyString("telegramBotToken");
		$telegramChatId = $this->ReadPropertyString("telegramChatId");

		$notifyMsg .= sprintf("\n<b>%s</b> <i>@%s</i>", gethostname(), date('d.m.Y H:i:s', time()));
		$scriptId = IPS_GetObjectIDByIdent ("telegramBot", $this->InstanceID);

        IPS_RunScriptEx($scriptId, Array("TelegramMessage" => $notifyMsg, "TelegramChatId" => $telegramChatId, "TelegramToken" => $telegramBotToken));

        if ($this->logLevel >= LogLevel::INFO) { $this->AddLog(__FUNCTION__, sprintf("Notify %s : ", $sender, $notifyMsg)); }
	}

}

?>