<?

abstract class LogLevel {
    const ALL = 9;
    const TEST = 8;
    const TRACE = 7;
    const COMMUNICATION = 6;
    const DEBUG = 5;
    const INFO = 4;
    const WARN = 3;
    const ERROR = 2;
    const FATAL = 1;
}

abstract class VARIABLE {
    const TYPE_BOOLEAN = 0;
    const TYPE_INTEGER = 1;
    const TYPE_FLOAT = 2;
    const TYPE_STRING = 3;
}


trait APRSCommon {

    protected function ParseRawDataNEW(string $rawData) {

        $time_start = microtime(true);
        $timeStamp = time();
        $dataArr = array();

        $latitudeADW20 = 48.325602;
        $longitudeADW20 = 14.426158;
        $altitudeADW20 = 354;

        $warnings = "";
        $errorMsg = "";

        try {

            $dataArr["rawData"] = $rawData;
            $dataArr["rawDataPart1"] = "init";
            $dataArr["rawDataPart1a"] = "init";
            $dataArr["rawDataPart1b"] = "init";
            $dataArr["rawDataPart2"] = "init";

            $dataArr["callSign"] = "init";
            $dataArr["qConstruct"]   = null;
            $dataArr["objName"]      = null;
            $dataArr["qConstruct"]   = null;
            $dataArr["timeStamp"]    = null;
            $dataArr["latitude"]     = null;
            $dataArr["longitude"]    = null;
            $dataArr["course"]       = null;
            $dataArr["speed"]        = null;
            $dataArr["altitude"]     = null;
            $dataArr["distPG1ADW"]   = null;

            $dataArr["Clb"] = null;
            $dataArr["p"] = null;
            $dataArr["t"]  = null;
            $dataArr["h"] = null;
            $dataArr["o3"] = null;
            $dataArr["ti"] = null;
            $dataArr["Pump"] = null;
            $dataArr["batt"] = null;
            $dataArr["MHz"] = null;
            $dataArr["Type"] = null;
            $dataArr["ser"] = null;
            $dataArr["FN"] = null;
            $dataArr["rssi"] = null;
            $dataArr["Sats"] = null;
            $dataArr["BK"] = null;
            $dataArr["rx"] = null;
            $dataArr["powerup"] = null;
            $dataArr["S"] = null;
            $dataArr["azimuth"] = null;
            $dataArr["distance"] = null;
            $dataArr["OG"] = null;
            $dataArr["elevation"] = null;
            $dataArr["dev"] = null;
            $dataArr["otherData"] = null;


            $pos = strpos($rawData, "/A=");
            if ($pos === false) {
                $warnings .= "'/A=' not found | ";

                $pos = strrpos($rawData, ">");
                if ($pos !== false) {
                    $dataArr["callSign"] = trim(substr($rawData, 0, $pos));
                } else {
                    $dataArr["callSign"] = "ParseERROR";
                }

                // - - - Parse 'q-construct' - - http://www.aprs-is.net/q.aspx - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  
                $pos1 = strpos($rawData, ",q");
                if ($pos1 === false) {
                    $dataArr["qConstruct"] = "ParseERROR";
                } else {
                    $dataArr["qConstruct"]  = substr($rawData, $pos1 + 1, 3);
                }
            } else {
                $dataArr["rawDataPart1"] = $rawDataPart1 = substr($rawData, 0, $pos + 9);
                $dataArr["rawDataPart2"] = $part2 = trim(substr($rawData, $pos + 9));

                $enableLogFile_3 = GetValue($this->GetIDForIdent("enableLogFile_3"));
                if ($enableLogFile_3) {
                    $this->WriteRawDataToLogFile(sprintf("Part 1 :: %s", $rawDataPart1));
                }

                if ($this->logLevel >= LogLevel::COMMUNICATION) {
					$this->AddLog(__FUNCTION__, sprintf("RawData Part1: '%s' ", $rawDataPart1));
				}

                $pos = strpos($rawData, ":;");
                if ($pos === false) {
                    $warnings .= "':;' not found | ";

                    $pos = strrpos($rawData, ">");
                    if ($pos !== false) {
                        $dataArr["callSign"] = trim(substr($rawData, 0, $pos));
                    } else {
                        $dataArr["callSign"] = "ParseERROR";
                    }

                    // - - - Parse 'q-construct' - - http://www.aprs-is.net/q.aspx - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  
                    $pos1 = strpos($rawData, ",q");
                    if ($pos1 === false) {
                        $dataArr["qConstruct"] = "ParseERROR";
                    } else {
                        $dataArr["qConstruct"]  = substr($rawData, $pos1 + 1, 3);
                    }
                } else {

                    $part1Arr = explode(":;", $rawDataPart1);
                    $arrCount = count($part1Arr);
                    $dataArr["rawDataPart1a"] = $rawDataPart1a = $part1Arr[0];
                    $dataArr["rawDataPart1b"] = $rawDataPart1b = $part1Arr[1];

                    if (1 == 1) {
                        $part1aArr = explode(",", $rawDataPart1a);
                        $pos = strrpos($part1aArr[0], ">");
                        if ($pos !== false) {
                            $dataArr["callSign"] = trim(substr($part1aArr[0], 0, $pos));
                        } else {
                            $dataArr["callSign"] = "ParseERROR";
                        }

                        $pos = strrpos($rawDataPart1b, "*");
                        if ($pos !== false) {
                            $objName = substr($rawDataPart1b, 0, $pos);
                            $dataArr["objName"] = trim($objName, "\xC2\xA0 ");
                        } else {
                            $dataArr["objName"] = "ParseERROR";
                        }

                        // - - - Parse 'q-construct' - - http://www.aprs-is.net/q.aspx - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  
                        $pos1 = strpos($rawDataPart1a, ",q");
                        if ($pos1 === false) {
                            $dataArr["qConstruct"] = "ParseERROR";
                        } else {
                            $dataArr["qConstruct"]  = substr($rawDataPart1a, $pos1 + 1, 3);
                        }


                        // - - - Parse 'Time' - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  
                        $pos1 = strpos($rawDataPart1b, "*");
                        if ($pos1 === false) {
                            $dataArr["timeStamp"] = null;
                        } else {
                            $pos2 = strpos($rawDataPart1b, "h", $pos1);
                            $timeTxt = substr($rawDataPart1b, $pos1, $pos2 - $pos1 + 1);
                            try {
                                $time = new DateTime(substr($timeTxt, 1, 6), new DateTimeZone('UTC'));
                            } catch (Exception $e) {
                                $time = new DateTime("2000-01-01 000000", new DateTimeZone('UTC'));
                            }
                            $dataArr["timeStamp"] = $time->getTimestamp();
                        }

                        // - - - Parse 'latitude' - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  
                        $pos1 = strpos($rawDataPart1b, "h");
                        if ($pos1 === false) {
                            $dataArr["latitude"] = null;
                        } else {
                            $pos2 = strpos($rawDataPart1b, "N", $pos1);
                            $latitudeTxt = substr($rawDataPart1b, $pos1 + 1, $pos2 - $pos1 - 1);
                            $dataArr["latitude"] = $this->ParseCoordinate($latitudeTxt);
                        }

                        // - - - Parse 'longitude' - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  
                        $pos1 = strpos($rawDataPart1b, "/");
                        if ($pos1 === false) {
                            $dataArr["longitude"] = null;
                        } else {
                            $pos2 = strpos($rawDataPart1b, "E", $pos1);
                            $longitudeTxt = substr($rawDataPart1b, $pos1 + 1, $pos2 - $pos1 - 1);
                            $dataArr["longitude"] = $this->ParseCoordinate($longitudeTxt);
                        }

                        // - - - Parse 'course' - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  
                        $pos1 = strpos($rawDataPart1b, "O", $pos1);
                        if ($pos1 === false) {
                            $dataArr["course"] = null;
                        } else {
                            $pos2 = strpos($rawDataPart1b, "/", $pos1);
                            $courseTxt = substr($rawDataPart1b, $pos1, $pos2 - $pos1);
                            $dataArr["course"] = floatval(substr($courseTxt, 1));
                        }

                        // - - - Parse 'speed' - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                        if ($pos2 > 0) {
                            $pos1 = $pos2 + 1;
                            $pos2 = strpos($rawDataPart1b, "/", $pos1);
                            $speedTxt = substr($rawDataPart1b, $pos1, $pos2 - $pos1);
                            $dataArr["speed"] = floatval($speedTxt);
                        } else {
                            $dataArr["speed"] = null;
                        }

                        // - - - Parse 'altitude' - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  
                        $pos1 = strpos($rawDataPart1b, "A=");
                        if ($pos1 === false) {
                            $dataArr["altitude"] = null;
                        } else {
                            $altitudeTxt = substr($rawDataPart1b, $pos1);
                            $dataArr["altitude"] = round(floatval(substr($altitudeTxt, 2)) * 0.3048, 1);     //Feed in Meter umwandeln
                        }

                        // - - - Calc 'distPG1ADW' - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  
                        if (is_null($dataArr["latitude"]) or is_null($dataArr["longitude"])) {
                            $dataArr["distPG1ADW"] = null;
                        } else {
                            $dataArr["distPG1ADW"] = $this->distance($dataArr["latitude"], $dataArr["longitude"], $latitudeADW20, $longitudeADW20, "K", 2);
                        }
                    }

                    //PARSE Part 2                
                    if (1 == 1) {

                        $enableLogFile_4 = GetValue($this->GetIDForIdent("enableLogFile_4"));
                        if ($enableLogFile_4) {
                            $this->WriteRawDataToLogFile(sprintf("Part 2 :: %s", $part2));
                        }

                        if ($this->logLevel >= LogLevel::COMMUNICATION) {
                            $this->AddLog(__FUNCTION__, sprintf("RawData Part2: '%s' ", $part2));
                        }                        
 
                        $rawDataPart2a = ""; 
                        $rawDataPart2b = ""; 
                        $part2Len = strlen($part2);
                        $pos1 = strpos($part2, "!");
                        if($pos !== false) {
                            $pos2 = $pos1 + 4;
                            if($part2[$pos2] == "!") {
                                $pos2 = strpos($part2, "!", $pos1+1);
                                $len = $pos2 - $pos1;
                                if($len == 4) {
                                    $rawDataPart2a = substr($part2, $pos1, $len+1);
                                    $rawDataPart2b = str_replace($rawDataPart2a, "", $part2);
                                }
                            }
                        }

                        $rawDataPart2b = trim($rawDataPart2b);
 
                        $otherData = "";
                        //$rawDataPart2b = str_replace("Clb=", " Clb=", $rawDataPart2b);
                        $rawDataPart2b = str_replace(" MHz ", "MHz ", $rawDataPart2b);
                        $rawDataPart2b = str_replace("powerup h:m:s ", " powerup=", $rawDataPart2b);
              
                        $dataArr["rawDataPart2a"] = $rawDataPart2a;
                        $dataArr["rawDataPart2b"] = $rawDataPart2b;

                        $part2Arr = explode(" ", $rawDataPart2b);
                        $part2ArrWorking = array();
                        foreach ($part2Arr as $value) {
                            $part2ArrWorking[] = "_" . $value;
                        }

                        $enableLogFile_5 = GetValue($this->GetIDForIdent("enableLogFile_5"));
                        if ($enableLogFile_5) {
                            $log = implode(" | ", $part2ArrWorking);
                            $this->WriteRawDataToLogFile(sprintf("Part 2 :: %s", $log));
                        }

                        foreach ($part2ArrWorking as $part2Entry) {

                            if (false !== $value = $this->ExtractFloatValue($part2Entry, "_Clb=")) {
                                $dataArr["Clb"] = $value;
                            } else if (false !== $value = $this->ExtractFloatValue($part2Entry, "_dist=")) {
                                $dataArr["distance"] = $value;
                            } else if (false !== $value = $this->ExtractFloatValue($part2Entry, "_t=")) {
                                $dataArr["t"] = $value;
                            } else if (false !== $value = $this->ExtractFloatValue($part2Entry, "_h=")) {
                                $dataArr["h"] = $value;
                            } else if (false !== $value = $this->ExtractFloatValue($part2Entry, "_p=")) {
                                $dataArr["p"] = $value;
                            } else if (false !== $value = $this->ExtractFloatValue($part2Entry, "_Sats=")) {
                                $dataArr["Sats"] = $value;
                            } else if (false !== $value = $this->ExtractFloatValue($part2Entry, "_ti=")) {
                                $dataArr["ti"] = $value;
                            } else if (false !== $value = $this->ExtractFloatValue($part2Entry, "_batt=")) {
                                $dataArr["batt"] = $value;
                            } else if (false !== $value = $this->ExtractFloatValue($part2Entry, "_V=")) {
                                $dataArr["batt"] = $value;
                            } else if (false !== $value = $this->ExtractStringValue($part2Entry, "_Type=")) {
                                $dataArr["Type"] = $value;
                            } else if (false !== $value = $this->ExtractFloatValue($part2Entry, "MHz")) {
                                $dataArr["MHz"] = $value;
                            } else if (false !== $value = $this->ExtractFloatValue($part2Entry, "_rssi=")) {
                                $dataArr["rssi"] = $value;
                            } else if (false !== $value = $this->ExtractFloatValue($part2Entry, "_FN=")) {
                                $dataArr["FN"] = $value;
                            } else if (false !== $value = $this->ExtractFloatValue($part2Entry, "_o3=")) {
                                $dataArr["o3"] = $value;
                            } else if (false !== $value = $this->ExtractFloatValue($part2Entry, "_Pump=")) {
                                $dataArr["Pump"] = $value;
                            } else if (false !== $value = $this->ExtractStringValue($part2Entry, "_ser=")) {
                                $dataArr["ser"] = $value;
                            } else if (false !== $value = $this->ExtractStringValue($part2Entry, "_powerup=")) {
                                $dataArr["powerup"] = $value;
                            } else if (false !== $value = $this->ExtractStringValue($part2Entry, "_BK=")) {
                                $dataArr["BK"] = $value;
                            } else if (false !== $value = $this->ExtractStringValue($part2Entry, "_rx=")) {
                                $dataArr["rx"] = $value;
                            } else if (false !== $value = $this->ExtractStringValue($part2Entry, "_S=")) {
                                $dataArr["S"] = $value;
                            } else if (false !== $value = $this->ExtractFloatValue($part2Entry, "_azimuth=")) {
                                $dataArr["azimuth"] = $value;
                            } else if (false !== $value = $this->ExtractFloatValue($part2Entry, "_OG=")) {
                                $dataArr["OG"] = $value;
                            } else if (false !== $value = $this->ExtractFloatValue($part2Entry, "_elevation=")) {
                                $dataArr["elevation"] = $value;
                            } else if (false !== $value = $this->ExtractStringValue($part2Entry, "_dev=")) {
                                $dataArr["dev"] = $value;
                            } else {
                                $otherData .=  ltrim($part2Entry, '_') . " ";
                            }
                        }

                        $dataArr["otherData"] = $otherData;
                    }
                }
            }
        } catch (Exception $e) {
            $errorMsg = sprintf("ERROR in Line '%s' > %s", $e->getLine(), $e->getMessage());
            if ($this->logLevel >= LogLevel::ERROR) {
                $this->AddLog(__FUNCTION__, $errorMsg, 0, true);
            }
            IPS_LogMessage( __CLASS__."-".__FUNCTION__, $errorMsg);
        }

        $dataArr["ParseDuration_ms"] = $this->CalcDuration_ms($time_start);
        $dataArr["LocalTimeStamp"] = $timeStamp;
        $dataArr["ParseResult"] = $errorMsg . " | " . $warnings;

        return $dataArr;
    }

    protected function ExtractFloatValue($dataRawStr, $name) {
        $pos = strpos($dataRawStr, $name);
        if ($pos === false) {
            if ($this->logLevel >= LogLevel::TEST) {
                $this->AddLog(__FUNCTION__, sprintf("could not find '%s' in '%s'", $name, $dataRawStr), 0, true);
            }
            return false;
        } else {
            $dataValueStr = str_replace($name, "", $dataRawStr);
            $dataValueStr = ltrim($dataValueStr, '_');
            $value = floatval($dataValueStr);
            if ($this->logLevel >= LogLevel::TEST) {
                $this->AddLog(__FUNCTION__, sprintf("found '%s' in '%s' @ Pos=%d | '%s' parsed to '%s'", $name, $dataRawStr, $pos, $dataValueStr, $value), 0, true);
            }
            return $value;
        }
    }

    protected function ExtractStringValue($dataRawStr, $name) {
        $pos = strpos($dataRawStr, $name);
        if ($pos === false) {
            if ($this->logLevel >= LogLevel::TEST) {
                $this->AddLog(__FUNCTION__, sprintf("could not find '%s' in '%s'", $name, $dataRawStr), 0, true);
            }
            return false;
        } else {
            $dataValueStr = str_replace($name, "", $dataRawStr);
            $value = ltrim($dataValueStr, '_');
            if ($this->logLevel >= LogLevel::TEST) {
                $this->AddLog(__FUNCTION__, sprintf("found '%s' in '%s' @ Pos=%d | value='%s'", $name, $dataRawStr, $pos, $value), 0, true);
            }
            return $value;
        }
    }


    protected function ParseRawData__OLD(string $rawData) {

        $time_start = microtime(true);
        $timeStamp = time();
        $dataArr = array();

        $latitudeADW20 = 48.325602;
        $longitudeADW20 = 14.426158;
        $altitudeADW20 = 354;


        try {

            $rawDataPart1 = "";
            $rawDataPart2 = "";

            $pos1 = strpos($rawData, "!");
            if ($pos1 === false) {

                $posSkip = strpos($rawData, "DB0FBG");
                if ($posSkip === false) {
                    // RawData: DB0RIE>APNW01,DB0INS-7*,WIDE2-1,qAU,DC1SK-15:@021822z5052.88N/01107.74E#PHG4750/APRS Digi - Riechheimer Berg
                    $logMsg = "WARN :: First Separator '!' NOT found > Skip RawData with 'DB0RIE' ...";
                    if ($this->logLevel >= LogLevel::WARN) {
                        $this->AddLog(__FUNCTION__, $logMsg, 0);
                    }
                    $dataArr["Result"] = false;
                    $dataArr["ResultMsg"] = $logMsg;
                    exit();
                } else {
                    $logMsg = "WARN :: First Separator '!' NOT found";
                    if ($this->logLevel >= LogLevel::WARN) {
                        $this->AddLog(__FUNCTION__, $logMsg, 0);
                    }
                    $dataArr["Result"] = false;
                    $dataArr["ResultMsg"] = $logMsg;
                }
            } else {
                $rawDataPart1 = substr($rawData, 0, $pos1);
                $rawDataPart2 = substr($rawData, $pos1 + 1);

                $pos2 = strrpos($rawDataPart2, "!");
                if ($pos2 === false) {

                    $logMsg = "WARNING :: Second Separator '!' NOT found";
                    if ($this->logLevel >= LogLevel::WARN) {
                        $this->AddLog(__FUNCTION__, $logMsg, 0);
                    }
                    $dataArr["Result"] = false;
                    $dataArr["ResultMsg"] = $logMsg;
                } else {
                    $rawDataPart2 = substr($rawDataPart2, $pos2 + 1);
                }

                if ($this->logLevel >= LogLevel::DEBUG) {
                    $this->AddLog(__FUNCTION__, sprintf("rawDataPart1: %s", $rawDataPart1), 0);
                }
                if ($this->logLevel >= LogLevel::DEBUG) {
                    $this->AddLog(__FUNCTION__, sprintf("rawDataPart2: %s", $rawDataPart2), 0);
                }

                // - - - Parse 'CET' - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  
                $pos1 = strpos($rawDataPart1, "CET");
                if ($pos1 === false) {
                    $dataArr["CET"] = null;
                } else {
                    $cetTxt = substr($rawDataPart1, 0, $pos1 - 1);
                    $cetTxt = str_replace("\n", '', $cetTxt);                             // remove new lines
                    $cetTxt = str_replace("\r", '', $cetTxt);                            // remove carriage returns
                    $cet = new DateTime($cetTxt, new DateTimeZone('UTC'));
                    $dataArr["CET"] = $cet->getTimestamp();                            //echo date('d.m.Y H:i:s', $objDataArr["time"]);
                }

                // - - - Parse 'sign' - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  
                $pos1 = strpos($rawDataPart1, ":;");
                if ($pos1 === false) {
                    $dataArr["srcCallSign"] = "-";
                } else {
                    $sign = substr($rawDataPart1, 0, $pos1);
                    $pos1 = strrpos($sign, ">");
                    $sign = substr($sign, 0, $pos1);
                    $pos1 = strrpos($sign, " ");
                    if ($pos1 === false) {
                        $dataArr["srcCallSign"] = $sign;
                    } else {
                        $dataArr["srcCallSign"] = substr($sign, $pos1 + 1);
                    }
                }

                // - - - Parse 'objName' - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  
                $pos1 = strpos($rawDataPart1, ":;");
                if ($pos1 === false) {
                    $dataArr["objName"] = "-";
                } else {
                    $pos2 = strpos($rawDataPart1, "*", $pos1);
                    $objName = substr($rawDataPart1, $pos1 + 2, $pos2 - $pos1 - 2);
                    $dataArr["objName"] = trim($objName, "\xC2\xA0 ");                //trimm 'non-breaking space' and 'Space'
                }

                // - - - Parse 'Time' - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  
                $pos1 = strpos($rawDataPart1, "*");
                if ($pos1 === false) {
                    $dataArr["time"] = null;
                } else {
                    $pos2 = strpos($rawDataPart1, "h", $pos1);
                    $timeTxt = substr($rawDataPart1, $pos1, $pos2 - $pos1 + 1);
                    try {
                        $time = new DateTime(substr($timeTxt, 1, 6), new DateTimeZone('UTC'));
                    } catch (Exception $e) {
                        $time = new DateTime("2000-01-01 000000", new DateTimeZone('UTC'));
                    }
                    $dataArr["time"] = $time->getTimestamp();
                }

                // - - - Parse 'latitude' - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  
                $pos1 = strpos($rawDataPart1, "h");
                if ($pos1 === false) {
                    $dataArr["latitude"] = null;
                } else {
                    $pos2 = strpos($rawDataPart1, "N", $pos1);
                    $latitudeTxt = substr($rawDataPart1, $pos1 + 1, $pos2 - $pos1 - 1);
                    $dataArr["latitude"] = $this->ParseCoordinate($latitudeTxt);
                }

                // - - - Parse 'longitude' - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  
                $pos1 = strpos($rawDataPart1, "/");
                if ($pos1 === false) {
                    $dataArr["longitude"] = null;
                } else {
                    $pos2 = strpos($rawDataPart1, "E", $pos1);
                    $longitudeTxt = substr($rawDataPart1, $pos1 + 1, $pos2 - $pos1 - 1);
                    $dataArr["longitude"] = $this->ParseCoordinate($longitudeTxt);
                }

                // - - - Calc 'distPG1ADW' - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  
                if (is_null($dataArr["latitude"]) or is_null($dataArr["longitude"])) {
                    $dataArr["distPG1ADW"] = null;
                } else {
                    $dataArr["distPG1ADW"] = $this->distance($dataArr["latitude"], $dataArr["longitude"], $latitudeADW20, $longitudeADW20, "K", 2);
                }

                // - - - Parse 'course' - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  
                $pos1 = strpos($rawDataPart1, "O", $pos1);
                if ($pos1 === false) {
                    $dataArr["course"] = null;
                } else {
                    $pos2 = strpos($rawDataPart1, "/", $pos1);
                    $courseTxt = substr($rawDataPart1, $pos1, $pos2 - $pos1);
                    $dataArr["course"] = floatval(substr($courseTxt, 1));
                }

                // - - - Parse 'speed' - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                if ($pos2 > 0) {
                    $pos1 = $pos2 + 1;
                    $pos2 = strpos($rawDataPart1, "/", $pos1);
                    $speedTxt = substr($rawDataPart1, $pos1, $pos2 - $pos1);
                    $dataArr["speed"] = floatval($speedTxt);
                } else {
                    $dataArr["speed"] = null;
                }

                // - - - Parse 'altitude' - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  
                $pos1 = strpos($rawDataPart1, "A=");
                if ($pos1 === false) {
                    $dataArr["altitude"] = null;
                } else {
                    $altitudeTxt = substr($rawDataPart1, $pos1);
                    $dataArr["altitude"] = round(floatval(substr($altitudeTxt, 2)) * 0.3048, 1);     //Feed in Meter umwandeln
                }

                // - - - Parse 'Clb' - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  
                $pos1 = strpos($rawDataPart2, "Clb=");
                if ($pos1 === false) {
                    $dataArr["Clb"] = null;
                } else {
                    $pos2 = strpos($rawDataPart2, "m/s", $pos1);
                    if ($pos2 === false) {
                        $ClbTxt = "-1";
                    } else {
                        $ClbTxt = substr($rawDataPart2, $pos1, $pos2 - $pos1 + 3);
                    }
                    $dataArr["Clb"] = floatval(substr($ClbTxt, 4));
                }

                // - - - Parse 'MHz' - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  
                $pos2 = strpos($rawDataPart2, "MHz");
                if ($pos2 === false) {
                    $dataArr["frequenz"] = null;
                } else {
                    $pos1 = strpos($rawDataPart2, ".", $pos2 - 5);
                    if ($pos1 === false) {
                        $frequenzTxt = "-1";
                    } else {
                        $frequenzTxt = substr($rawDataPart2, $pos1 - 3, $pos2 - $pos1 + 6);
                    }
                    $dataArr["frequenz"] = floatval($frequenzTxt);
                }

                // - - - Parse p (hPa) - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  
                $pos1 = strpos($rawDataPart2, "p=");
                if ($pos1 === false) {
                    $dataArr["p"] = null;
                } else {
                    $pos2 = strpos($rawDataPart2, "hPa", $pos1);
                    if ($pos2 === false) {
                        $hPaTxt = "-1";
                    } else {
                        $hPaTxt = substr($rawDataPart2, $pos1, $pos2 - $pos1 + 1);
                    }
                    $dataArr["p"] = floatval(substr($hPaTxt, 2));
                }

                // - - - Parse t (°C) - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  
                $pos1 = strpos($rawDataPart2, "t=");
                if ($pos1 === false) {
                    $dataArr["t"] = null;
                } else {
                    $pos2 = strpos($rawDataPart2, "C", $pos1);
                    if ($pos2 === false) {
                        $tTxt = "-1";
                    } else {
                        $tTxt = substr($rawDataPart2, $pos1, $pos2 - $pos1 + 1);
                    }
                    $dataArr["t"] = floatval(substr($tTxt, 2));
                }

                // - - - Parse h (%) - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  
                $pos1 = strpos($rawDataPart2, "h=");
                if ($pos1 === false) {
                    $dataArr["h"] = null;
                } else {
                    $pos2 = strpos($rawDataPart2, "%", $pos1);
                    if ($pos2 === false) {
                        $hTxt = "-1";
                    } else {
                        $hTxt = substr($rawDataPart2, $pos1, $pos2 - $pos1 + 1);
                    }
                    $dataArr["h"] = floatval(substr($hTxt, 2));
                }

                // - - - Parse o3 (mPa) - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  
                $pos1 = strpos($rawDataPart2, "o3=");
                if ($pos1 === false) {
                    $dataArr["o3"] = null;
                } else {
                    $pos2 = strpos($rawDataPart2, "mPa", $pos1);
                    if ($pos2 === false) {
                        $o3Txt = "-1";
                    } else {
                        $o3Txt = substr($rawDataPart2, $pos1, $pos2 - $pos1 + 3);
                    }
                    $dataArr["o3"] = floatval(substr($o3Txt, 3));
                }

                // - - - Parse ti (°C) - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  
                $pos1 = strpos($rawDataPart2, "ti=");
                if ($pos1 === false) {
                    $dataArr["ti"] = null;
                } else {
                    $pos2 = strpos($rawDataPart2, "C", $pos1);
                    if ($pos2 === false) {
                        $tiTxt = "-1";
                    } else {
                        $tiTxt = substr($rawDataPart2, $pos1, $pos2 - $pos1 + 1);
                    }
                    $dataArr["ti"] = floatval(substr($tiTxt, 3));
                }

                // - - - Parse Type - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  
                $pos1 = strpos($rawDataPart2, "Type=");
                if ($pos1 === false) {
                    $dataArr["type"] = "-";
                } else {
                    $pos2 = strpos($rawDataPart2, " ", $pos1);
                    if ($pos2 === false) {
                        $type = substr($rawDataPart2, $pos1);
                    } else {
                        $type = substr($rawDataPart2, $pos1, $pos2 - $pos1);
                    }
                    $dataArr["type"] = substr($type, 5);
                }

                // - - - Parse FN  - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  
                $pos1 = strpos($rawDataPart2, "FN=");
                if ($pos1 === false) {
                    $dataArr["FN"] = null;
                } else {
                    $pos2 = strpos($rawDataPart2, " ", $pos1);
                    if ($pos2 === false) {
                        $FNTxt = substr($rawDataPart2, $pos1);
                    } else {
                        $FNTxt = substr($rawDataPart2, $pos1, $pos2 - $pos1);
                    }
                    $dataArr["FN"] = floatval(substr($FNTxt, 3));
                }

                // - - - Parse rssi  - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  
                $pos1 = strpos($rawDataPart2, "rssi=");
                if ($pos1 === false) {
                    $dataArr["rssi"] = null;
                } else {
                    $pos2 = strpos($rawDataPart2, "dB", $pos1);
                    if ($pos2 === false) {
                        $rssiTxt = "-1";
                    } else {
                        $rssiTxt = substr($rawDataPart2, $pos1, $pos2 - $pos1 + 2);
                    }
                    $dataArr["rssi"] = floatval(substr($rssiTxt, 5));
                }

                // - - - Parse Sats - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  
                $pos1 = strpos($rawDataPart2, "Sats=");
                if ($pos1 === false) {
                    $dataArr["Sats"] = null;
                } else {
                    $pos2 = strpos($rawDataPart2, " ", $pos1);
                    if ($pos2 === false) {
                        $SatsTxt = substr($rawDataPart2, $pos1);
                    } else {
                        $SatsTxt = substr($rawDataPart2, $pos1, $pos2 - $pos1);
                    }
                    $dataArr["Sats"] = floatval(substr($SatsTxt, 5));
                }

                // - - - Parse OG/posambiguity- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  
                $pos1 = strpos($rawDataPart2, "OG=");
                if ($pos1 === false) {
                    $dataArr["OG"] = null;
                } else {
                    $pos2 = strpos($rawDataPart2, "m", $pos1);
                    if ($pos2 === false) {
                        $posambiguityTxt = "-1";
                    } else {
                        $posambiguityTxt = substr($rawDataPart2, $pos1, $pos2 - $pos1);
                    }
                    $dataArr["OG"] = floatval(substr($posambiguityTxt, 3));
                }

                // - - - Parse Azimuth - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  
                $pos1 = strpos($rawDataPart2, "azimuth=");
                if ($pos1 === false) {
                    $dataArr["azimuth"] = null;
                } else {
                    $pos2 = strpos($rawDataPart2, " ", $pos1);
                    if ($pos2 === false) {
                        $azimuthTxt = substr($rawDataPart2, $pos1);
                    } else {
                        $azimuthTxt = substr($rawDataPart2, $pos1, $pos2 - $pos1);
                    }
                    $dataArr["azimuth"] = intval(substr($azimuthTxt, 8));
                }

                // - - - Parse Distance - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  
                $pos1 = strpos($rawDataPart2, "dist=");
                if ($pos1 === false) {
                    $dataArr["distance"] = null;
                } else {
                    $pos2 = strpos($rawDataPart2, " ", $pos1);
                    if ($pos2 === false) {
                        $distanceTxt = substr($rawDataPart2, $pos1);
                    } else {
                        $distanceTxt = substr($rawDataPart2, $pos1, $pos2 - $pos1);
                    }
                    $dataArr["distance"] = floatval(substr($distanceTxt, 5));
                }

                // - - - Parse Elevation - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  
                $pos1 = strpos($rawDataPart2, "elevation=");
                if ($pos1 === false) {
                    $dataArr["elevation"] = null;
                } else {
                    $pos2 = strpos($rawDataPart2, " ", $pos1);
                    if ($pos2 === false) {
                        $elevationTxt = substr($rawDataPart2, $pos1);
                    } else {
                        $elevationTxt = substr($rawDataPart2, $pos1, $pos2 - $pos1);
                    }
                    $dataArr["elevation"] = floatval(substr($elevationTxt, 10));
                }

                // - - - Parse rx - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  
                $pos1 = strpos($rawDataPart2, "rx=");
                if ($pos1 === false) {
                    $dataArr["rx"] = null;
                } else {
                    $pos2 = strpos($rawDataPart2, " ", $pos1);
                    if ($pos2 === false) {
                        $dataArr["rx"] = substr($rawDataPart2, $pos1);
                    } else {
                        $dataArr["rx"] = substr($rawDataPart2, $pos1, $pos2 - $pos1);
                    }
                }

                // - - - Parse dev - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  
                $pos1 = strpos($rawDataPart2, "dev=");
                if ($pos1 === false) {
                    $dataArr["dev"] = null;
                } else {
                    $pos2 = strpos($rawDataPart2, " ", $pos1);
                    if ($pos2 === false) {
                        $dataArr["dev"] = substr($rawDataPart2, $pos1 + 4);
                    } else {
                        $dataArr["dev"] = substr($rawDataPart2, $pos1 + 4, $pos2 - $pos1 - 4);
                    }
                }

                // - - - Parse GPS horizontal noise - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  
                $pos1 = strpos($rawDataPart2, "hdil=");
                if ($pos1 === false) {
                    $dataArr["hdil"] = null;
                } else {
                    $pos2 = strpos($rawDataPart2, " ", $pos1);
                    if ($pos2 === false) {
                        $hdilTxt = substr($rawDataPart2, $pos1);
                    } else {
                        $hdilTxt = substr($rawDataPart2, $pos1, $pos2 - $pos1);
                    }
                    $dataArr["hdil"] = intval(substr($hdilTxt, 5));
                }


                // - - - Parse powerup h:m:s - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  
                $pos1 = strpos($rawDataPart2, "powerup h:m:s");
                if ($pos1 === false) {
                    $dataArr["powerup"] = null;
                } else {
                    $powerupTxt = substr($rawDataPart2, $pos1 + 14, 8);
                    $dataArr["powerup"] = $powerupTxt;
                }

                // - - - Parse sondemod - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  

                $pos1 = strpos($rawDataPart2, "sondemod(c)");
                if ($pos1 === false) {

                    $pos1 = strpos($rawDataPart2, "sondemod");
                    if ($pos1 === false) {
                        $dataArr["sondemod"] = null;
                    } else {
                        $pos2 = @strpos($rawDataPart2, " ", $pos1 + 9);
                        if ($pos2 === false) {
                            $dataArr["sondemod"] = substr($rawDataPart2, $pos1);
                        } else {
                            $dataArr["sondemod"] = substr($rawDataPart2, $pos1, $pos2 - $pos1);
                        }
                    }
                } else {
                    $pos2 = strpos($rawDataPart2, " ", $pos1 + 12);
                    if ($pos2 === false) {
                        $dataArr["sondemod"] = substr($rawDataPart2, $pos1);
                    } else {
                        $dataArr["sondemod"] = substr($rawDataPart2, $pos1, $pos2 - $pos1);
                    }
                }

                $dataArr["Result"] = true;
            }
        } catch (Exception $e) {
            $errorMsg = sprintf("ERROR in Line '%s' > %s", $e->getLine(), $e->getMessage());
            $dataArr["Result"] = false;
            $dataArr["ResultMsg"] = $errorMsg;
            if ($this->logLevel >= LogLevel::ERROR) {
                $this->AddLog(__FUNCTION__, $errorMsg, 0, true);
            }
        }

        $dataArr["LocalTimeStamp"] = $timeStamp;

        $time_end = microtime(true);
        $duration = $time_end - $time_start;
        $dataArr["Duration"] = round($duration * 1000, 2);

        $dataArr["RawDataPart1"] = $rawDataPart1;
        $dataArr["RawDataPart2"] = $rawDataPart2;
        $dataArr["RawData"] = $rawData;

        return $dataArr;
    }

    protected function FormatFloat($floatNumber) {
        return str_replace(",", ".", $floatNumber);
    }

    protected function ParseCoordinate($coordinateTxt) {

        $coordinateTEMP = floatval($coordinateTxt);
        $coordinateDEG = floor($coordinateTEMP / 100);
        $coordinateMIN = $coordinateTEMP - $coordinateDEG * 100;
        //$coordinateTxt1 = $longitudeDEG . "° " . $longitudeMIN . "'";
        return round($this->DMStoDD($coordinateDEG, $coordinateMIN, 0), 6);
    }

    protected function DMStoDD($deg, $min, $sec) {           // Converting DMS ( Degrees / minutes / seconds ) to decimal format
        return $deg + ((($min * 60) + ($sec)) / 3600);
    }

    protected function DDtoDMS($dec)  {                      // Converts decimal format to DMS ( Degrees / minutes / seconds ) 
        $vars = explode(".", $dec);
        $deg = $vars[0];
        $tempma = "0." . $vars[1];

        $tempma = $tempma * 3600;
        $min = floor($tempma / 60);
        $sec = $tempma - ($min * 60);
        return array("deg" => $deg, "min" => $min, "sec" => $sec);
    }


    /*::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::*/
    /*::                                                                         :*/
    /*::  This routine calculates the distance between two points (given the     :*/
    /*::  latitude/longitude of those points). It is being used to calculate     :*/
    /*::  the distance between two locations using GeoDataSource(TM) Products    :*/
    /*::                                                                         :*/
    /*::  Definitions:                                                           :*/
    /*::    South latitudes are negative, east longitudes are positive           :*/
    /*::                                                                         :*/
    /*::  Passed to function:                                                    :*/
    /*::    lat1, lon1 = Latitude and Longitude of point 1 (in decimal degrees)  :*/
    /*::    lat2, lon2 = Latitude and Longitude of point 2 (in decimal degrees)  :*/
    /*::    unit = the unit you desire for results                               :*/
    /*::           where: 'M' is statute miles (default)                         :*/
    /*::                  'K' is kilometers                                      :*/
    /*::                  'N' is nautical miles                                  :*/
    /*::  Worldwide cities and other features databases with latitude longitude  :*/
    /*::  are available at https://www.geodatasource.com                          :*/
    /*::                                                                         :*/
    /*::  For enquiries, please contact sales@geodatasource.com                  :*/
    /*::                                                                         :*/
    /*::  Official Web site: https://www.geodatasource.com                        :*/
    /*::                                                                         :*/
    /*::         GeoDataSource.com (C) All Rights Reserved 2018                  :*/
    /*::                                                                         :*/
    /*::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::*/
    protected function distance($lat1, $lon1, $lat2, $lon2, $unit, $round) {
        if (($lat1 == $lat2) && ($lon1 == $lon2)) {
            return 0;
        } else {
            $theta = $lon1 - $lon2;
            $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
            $dist = acos($dist);
            $dist = rad2deg($dist);
            $miles = $dist * 60 * 1.1515;
            $unit = strtoupper($unit);

            if ($unit == "K") {
                return round(($miles * 1.609344), $round);
            } else if ($unit == "N") {
                return round(($miles * 0.8684), $round);
            } else {
                return round($miles, $round);
            }
        }
    }

    protected function CalcDuration_ms($time_start) {
        $duration = microtime(true) - $time_start;
        return round($duration * 1000, 2);;
    }

    protected function AddLog11($name, $daten, $format) {
        $this->SendDebug("[" . __CLASS__ . "] - " . $name, $daten, $format);

        if ($this->enableIPSLogOutput) {
            if ($format == 0) {
                IPS_LogMessage("[" . __CLASS__ . "] - " . $name, $daten);
            } else {
                IPS_LogMessage("[" . __CLASS__ . "] - " . $name, $this->String2Hex($daten));
            }
        }
    }

    //////////////////////////////////////////////////////////////////////////////////////

    protected function SaveToDB($dataArr) {

        $time_start = microtime(true);
        $insertedDbRows = 0;

        try {

            $servername = "10.0.10.111:3306";
            $username = "ips";
            $password = "insertDB";
            $dbname = "MySQLDB";

            $mysqli = mysqli_init();
            if (!$mysqli) {
                $errorMsg = "ERROR >> mysqli_init failed";
                throw new Exception($errorMsg);
                //die($errorMsg);            
            }
            $mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 3);
            $mysqli->real_connect($servername, $username, $password);

            /* check connection */
            if ($mysqli->connect_errno) {
                $errorMsg = sprintf("MySQL Connect failed: %s", $mysqli->connect_error);
                throw new Exception($errorMsg);
            }

            /* check if server is alive */
            if ($mysqli->ping()) {
                if ($this->logLevel >= LogLevel::DEBUG) {
                    $this->AddLog(__FUNCTION__, "MySQL Ping OK", 0);
                }
            } else {
                if ($this->logLevel >= LogLevel::WARN) {
                    $this->AddLog(__FUNCTION__, sprintf("MySQL Ping failed: %s", $mysqli->error), 0);
                }
            }

            // $mysqli  = new mysqli($servername, $username, $password);
            // if ($mysqli->connect_error) {
            //     $errorMsg = "ERROR >> Database Connection failed: " . $mysqli->connect_error;
            //     throw new Exception($errorMsg);
            //     //die($errorMsg);
            // }

            //i	corresponding variable has type integer
            //d	corresponding variable has type double
            //s	corresponding variable has type string
            //b	corresponding variable is a blob and will be sent in packets


            $sql = "INSERT INTO `PG`.`APRS` "
                . "(`callSign`,`qConstruct`,`objName`,`timeStamp`,`latitude`,`longitude`,`altitude`,`course`,`speed`, "
                . "`Type`,`MHz`,`Sats`,`clb`,`p`,`t`,`h`,`o3`,`ti`,`pump`,`batt`,`ser`,`FN`,`rssi`,`S`,`rx`,`BK`, `powerup`, "
                . "`azimuth`,`distance`,`OG`,`elevation`,`dev`,`otherData`,`distPG1ADW`, "
                . "`rawData`,`rawDataPart1`,`rawDataPart2`,`rawDataPart1a`,`rawDataPart1b`, "
                . "`LocalTimeStamp`,`ParseDuration_ms`,`ParseResult`) "
                . " VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";


            $stmt = $mysqli->prepare($sql);
            if (!$stmt) {
                if ($this->logLevel >= LogLevel::ERROR) {
                    $this->AddLog(__FUNCTION__, sprintf("MySQL Prepare Error > %s  ", $mysqli->error), 0, true);
                }
            } else {

                //$this->AddLog(__FUNCTION__, sprintf("  @%s", print_r($dataArr, true)), 0);

                $callSign               = $this->GetArrayValue($dataArr, "callSign");
                $qConstruct             = $this->GetArrayValue($dataArr, "qConstruct");
                $objName                 = $this->GetArrayValue($dataArr, "objName");
                $timeStamp               = $this->GetArrayValue($dataArr, "timeStamp");
                $latitude               = $this->GetArrayValue($dataArr, "latitude");
                $longitude               = $this->GetArrayValue($dataArr, "longitude");
                $altitude               = $this->GetArrayValue($dataArr, "altitude");
                $course                 = $this->GetArrayValue($dataArr, "course");
                $speed                     = $this->GetArrayValue($dataArr, "speed");
                $Type                     = $this->GetArrayValue($dataArr, "Type");
                $MHz                       = $this->GetArrayValue($dataArr, "MHz");
                $Sats                     = $this->GetArrayValue($dataArr, "Sats");
                $clb                       = $this->GetArrayValue($dataArr, "Clb");
                $p                           = $this->GetArrayValue($dataArr, "p");
                $t                           = $this->GetArrayValue($dataArr, "t");
                $h                           = $this->GetArrayValue($dataArr, "h");
                $o3                       = $this->GetArrayValue($dataArr, "o3");
                $ti                       = $this->GetArrayValue($dataArr, "ti");
                $pump                     = $this->GetArrayValue($dataArr, "Pump");
                $batt                     = $this->GetArrayValue($dataArr, "batt");
                $ser                       = $this->GetArrayValue($dataArr, "ser");
                $FN                       = $this->GetArrayValue($dataArr, "FN");
                $rssi                     = $this->GetArrayValue($dataArr, "rssi");
                $S                           = $this->GetArrayValue($dataArr, "S");
                $rx                       = $this->GetArrayValue($dataArr, "rx");
                $BK                       = $this->GetArrayValue($dataArr, "BK");
                $powerup                 = $this->GetArrayValue($dataArr, "powerup");
                $azimuth                 = $this->GetArrayValue($dataArr, "azimuth");
                $distance               = $this->GetArrayValue($dataArr, "distance");
                $OG                        = $this->GetArrayValue($dataArr, "OG");
                $elevation               = $this->GetArrayValue($dataArr, "elevation");
                $dev                       = $this->GetArrayValue($dataArr, "dev");
                $otherData               = $this->GetArrayValue($dataArr, "otherData");
                $distPG1ADW             = $this->GetArrayValue($dataArr, "distPG1ADW");
                $rawData                 = $this->GetArrayValue($dataArr, "rawData");
                $rawDataPart1           = $this->GetArrayValue($dataArr, "rawDataPart1");
                $rawDataPart2           = $this->GetArrayValue($dataArr, "rawDataPart2");
                $rawDataPart1a           = $this->GetArrayValue($dataArr, "rawDataPart1a");
                $rawDataPart1b           = $this->GetArrayValue($dataArr, "rawDataPart1b");
                $LocalTimeStamp         = date('Y-m-d H:i:s', time());
                $ParseDuration_ms       = $this->GetArrayValue($dataArr, "ParseDuration_ms");
                $ParseResult             = $this->GetArrayValue($dataArr, "ParseResult");

                $len = strlen($Type);
                if ($len >= 12) {
                    IPS_LogMessage("[" . __CLASS__ . "] - " . __FUNCTION__, "PROBLEM >> Type to long >" . $Type);
                    $Type = substr($Type, 0, 11);
                }


                $stmt->bind_param(
                    'sssidddddsdiddddddddsiddsssididssdssssssds',
                    $callSign,
                    $qConstruct,
                    $objName,
                    $timeStamp,
                    $latitude,
                    $longitude,
                    $altitude,
                    $course,
                    $speed,
                    $Type,
                    $MHz,
                    $Sats,
                    $clb,
                    $p,
                    $t,
                    $h,
                    $o3,
                    $ti,
                    $pump,
                    $batt,
                    $ser,
                    $FN,
                    $rssi,
                    $S,
                    $rx,
                    $BK,
                    $powerup,
                    $azimuth,
                    $distance,
                    $OG,
                    $elevation,
                    $dev,
                    $otherData,
                    $distPG1ADW,
                    $rawData,
                    $rawDataPart1,
                    $rawDataPart2,
                    $rawDataPart1a,
                    $rawDataPart1b,
                    $LocalTimeStamp,
                    $ParseDuration_ms,
                    $ParseResult
                );

                $executeResult = $stmt->execute();
                if ($executeResult) {

                    $insertedDbRows = $stmt->affected_rows;
                    $insertId = $stmt->insert_id;
                    SetValue($this->GetIDForIdent("dbInsertId"), $insertId);
                    if ($this->logLevel >= LogLevel::DEBUG) {
                        $this->AddLog(__FUNCTION__, sprintf("DB Rows inserted : %s [InsertID: %s]", $insertedDbRows, $insertId), 0);
                    }

                    if ($insertedDbRows < 1) {
                        if ($this->logLevel >= LogLevel::WARN) {
                            $this->AddLog(__FUNCTION__, sprintf("WARN 'affected_rows' inserted : %s [InsertID: %s]", $insertedDbRows, $insertId), 0);
                        }
                        if ($this->logLevel >= LogLevel::WARN) {
                            $this->AddLog(__FUNCTION__, sprintf("  @%s", $dataArr["rawData"]), 0);
                        }
                        if ($this->logLevel >= LogLevel::WARN) {
                            $this->AddLog(__FUNCTION__, sprintf("  @%s", print_r($dataArr, true)), 0);
                        }
                    }
                } else {
                    if ($this->logLevel >= LogLevel::ERROR) {
                        $this->AddLog(__FUNCTION__, sprintf("MySQL bind_params Error > %s | %s", $mysqli->error, $stmt->error), 0, true);
                    }
                }
            }
        } catch (Exception $e) {
            $errorMsg = sprintf("ERROR in Line '%s' > %s", $e->getLine(), $e->getMessage());
            if ($this->logLevel >= LogLevel::ERROR) {
                $this->AddLog(__FUNCTION__, $errorMsg, 0, true);
            }
        } finally {
            $mysqli->close();
            $mysqli = null;
        }

        $dbInsertDuration = $this->CalcDuration_ms($time_start);
        SetValue($this->GetIDForIdent("dbInsertDuration"), $dbInsertDuration);

        return $insertedDbRows;
    }

    protected function GetArrayValue($dataArr, $key) {
        if (array_key_exists($key, $dataArr)) {
            return $dataArr[$key];
        } else {
            return NULL;
        }
    }

    protected function SaveToDB_OLD($dataArr) {

        $time_start = microtime(true);
        $insertedDbRows = 0;

        try {

            $servername = "127.0.0.1:3306";
            $username = "ips";
            $password = "insertDB!";
            //$dbname = "MySQLDB";
            $dbname = "PG";

            $mysqli = mysqli_init();
            if (!$mysqli) {
                $errorMsg = "ERROR >> mysqli_init failed";
                throw new Exception($errorMsg);
                //die($errorMsg);            
            }
            $mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 3);
            $mysqli->real_connect($servername, $username, $password);

            /* check connection */
            if ($mysqli->connect_errno) {
                $errorMsg = sprintf("MySQL Connect failed: %s", $mysqli->connect_error);
                throw new Exception($errorMsg);
            }

            /* check if server is alive */
            if ($mysqli->ping()) {
                if ($this->logLevel >= LogLevel::DEBUG) {
                    $this->AddLog(__FUNCTION__, "MySQL Ping OK", 0);
                }
            } else {
                if ($this->logLevel >= LogLevel::WARN) {
                    $this->AddLog(__FUNCTION__, sprintf("MySQL Ping failed: %s", $mysqli->error), 0);
                }
            }

            // $mysqli  = new mysqli($servername, $username, $password);
            // if ($mysqli->connect_error) {
            //     $errorMsg = "ERROR >> Database Connection failed: " . $mysqli->connect_error;
            //     throw new Exception($errorMsg);
            //     //die($errorMsg);
            // }

            //i	corresponding variable has type integer
            //d	corresponding variable has type double
            //s	corresponding variable has type string
            //b	corresponding variable is a blob and will be sent in packets

            $sql = "INSERT INTO `PG`.`APRS` "
                . " (`CET`, `srcCallSign`, `objName`, `time`, `latitude`, `longitude`, `altitude`, `course`, `speed`, "
                . " `clb`, `frequenz`, `p`, `t`, `h`, `o3`, `ti`, `type`, `FN`, `rssi`, `sats`, `OG`, `azimuth`, `distance`, `elevation`, `rx`, `dev`, `hdil`, `powerup`, `sondemod`, "
                . " `Result`, `Duration`, `LocalTimeStamp`, `RawDataPart1`, `RawDataPart2`, `distPG1ADW`, `Note`) "
                . " VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            if ($stmt = $mysqli->prepare($sql)) {

                $stmt->bind_param(
                    'issiddddddddddddsidiiiddssdssidsssds',
                    $CET,
                    $srcCallSign,
                    $objName,
                    $time,
                    $latitude,
                    $longitude,
                    $altitude,
                    $course,
                    $speed,
                    $clb,
                    $frequenz,
                    $p,
                    $t,
                    $h,
                    $o3,
                    $ti,
                    $type,
                    $FN,
                    $rssi,
                    $Sats,
                    $OG,
                    $azimuth,
                    $distance,
                    $elevation,
                    $rx,
                    $dev,
                    $hdil,
                    $powerup,
                    $sondemod,
                    $Result,
                    $Duration,
                    $LocalTimeStamp,
                    $RawDataPart1,
                    $RawDataPart2,
                    $distPG1ADW,
                    $note
                );

                $CET = $dataArr["CET"];
                $srcCallSign = $dataArr["srcCallSign"];
                $objName = $dataArr["objName"];
                $time = $dataArr["time"];
                $latitude = $dataArr["latitude"];
                $longitude = $dataArr["longitude"];
                $altitude = $dataArr["altitude"];
                $course = $dataArr["course"];
                $speed = $dataArr["speed"];
                $clb = $dataArr["Clb"];
                $frequenz = $dataArr["frequenz"];
                $p = $dataArr["p"];
                $t = $dataArr["t"];
                $h = $dataArr["h"];
                $o3 = $dataArr["o3"];
                $ti = $dataArr["ti"];
                $type = $dataArr["type"];
                $FN = $dataArr["FN"];
                $rssi = $dataArr["rssi"];
                $Sats = $dataArr["Sats"];
                $OG = $dataArr["OG"];
                $azimuth = $dataArr["azimuth"];
                $distance = $dataArr["distance"];
                $elevation = $dataArr["elevation"];
                $rx = $dataArr["rx"];
                $dev = $dataArr["dev"];
                $hdil = $dataArr["hdil"];
                $powerup = $dataArr["powerup"];
                $sondemod = $dataArr["sondemod"];
                $Result = $dataArr["Result"];
                $Duration = $dataArr["Duration"];
                $LocalTimeStamp = date('Y-m-d H:i:s', time());   //$dataArr["LocalTimeStamp"];
                $RawDataPart1 = substr($dataArr["RawDataPart1"], 0, 198);
                $RawDataPart2 = substr($dataArr["RawDataPart2"], 0, 198);
                $distPG1ADW = $dataArr["distPG1ADW"];
                $note = $dataArr["RawData"];

                $stmt->execute();
            } else {
                if ($this->logLevel >= LogLevel::ERROR) {
                    $this->AddLog(__FUNCTION__, sprintf("MySQL bind_params Error > %s", $mysqli->error), 0, true);
                }
            }
            $insertedDbRows = $stmt->affected_rows;
            $insertId = $stmt->insert_id;
            SetValue($this->GetIDForIdent("dbInsertId"), $insertId);
            if ($this->logLevel >= LogLevel::DEBUG) {
                $this->AddLog(__FUNCTION__, sprintf("DB Rows inserted : %s [InsertID: %s]", $insertedDbRows, $insertId), 0);
            }
            if ($insertedDbRows < 1) {
                if ($this->logLevel >= LogLevel::WARN) {
                    $this->AddLog(__FUNCTION__, sprintf("WARN 'affected_rows' inserted : %s [InsertID: %s]", $insertedDbRows, $insertId), 0);
                }
                if ($this->logLevel >= LogLevel::WARN) {
                    $this->AddLog(__FUNCTION__, sprintf("  @%s", $note), 0);
                }
                if ($this->logLevel >= LogLevel::WARN) {
                    $this->AddLog(__FUNCTION__, sprintf("  @%s", print_r($dataArr, true)), 0);
                }
            }

            /*
        $sql = "INSERT INTO `PG`.`APRS` (`CET`, `srcCallSign`, `objName`, `time`, `latitude`, `longitude`, `altitude`, `course`, `speed`, `clb`, `frequenz`, `p`, `t`, `h`, `o3`, `ti`, `type`, `FN`, `rssi`, `sats`, `OG`, `azimuth`, `distance`, `elevation`, `rx`, `dev`, `hdil`, `powerup`, `sondemod`, `Result`, `Duration`, `LocalTimeStamp`, `RawDataPart1`, `RawDataPart2`, `distPG1ADW`, `Note`) VALUES ('10', '20', '30', '40', '50', '60', '70', '80', '90', '101', '110', '120', '130', '140', '150', '160', '170', '180', '190', '200', '210', '220', '230', '240', '250', '260', '270', '280', '290', '300', '310', '2021-01-11 15:33:00', '330', '340', '350', '360')";
        if ($mysqli->query($sql) === TRUE) {
        echo "New record created successfully";
        } else {
        echo "Error: " . $sql . "<br>" . $mysqli->error;
        }
        */
        } catch (Exception $e) {
            $errorMsg = sprintf("ERROR in Line '%s' > %s", $e->getLine(), $e->getMessage());
            if ($this->logLevel >= LogLevel::ERROR) {
                $this->AddLog(__FUNCTION__, $errorMsg, 0, true);
            }
        } finally {
            $mysqli->close();
            $mysqli = null;
        }

        return $insertedDbRows;
    }

    protected function SaveRawDataToDB($rawDataTxt) {

        $time_start = microtime(true);
        $insertedDbRows = 0;

        try {

            $servername = "10.0.0.10:3306";
            $username = "ADMIN";
            $password = "dbPreinfalkG74!";
            $dbname = "MySQLDB";

            $mysqli = mysqli_init();
            if (!$mysqli) {
                $errorMsg = "ERROR >> mysqli_init failed";
                throw new Exception($errorMsg);
                //die($errorMsg);            
            }
            $mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 3);
            $mysqli->real_connect($servername, $username, $password);

            /* check connection */
            if ($mysqli->connect_errno) {
                $errorMsg = sprintf("MySQL Connect failed: %s", $mysqli->connect_error);
                throw new Exception($errorMsg);
            }

            /* check if server is alive */
            if ($mysqli->ping()) {
                if ($this->logLevel >= LogLevel::DEBUG) {
                    $this->AddLog(__FUNCTION__ . "_raw", "MySQL Ping OK", 0);
                }
            } else {
                if ($this->logLevel >= LogLevel::WARN) {
                    $this->AddLog(__FUNCTION__ . "_raw", sprintf("MySQL Ping failed: %s", $mysqli->error), 0);
                }
            }

            $sql = "INSERT INTO `PG`.`APRS_RAWDATA` "
                . " (`LocalTimeStamp`, `RawData`) "
                . " VALUES (?, ?)";

            if ($stmt = $mysqli->prepare($sql)) {

                $stmt->bind_param('ss', $LocalTimeStamp, $RawData);
                $LocalTimeStamp = date('Y-m-d H:i:s', time());   //$dataArr["LocalTimeStamp"];
                $RawData = $rawDataTxt;
                $stmt->execute();
            } else {
                if ($this->logLevel >= LogLevel::ERROR) {
                    $this->AddLog(__FUNCTION__ . "_raw", sprintf("MySQL bind_params Error > %s", $mysqli->error), 0, true);
                }
            }
            $insertedDbRows = $stmt->affected_rows;
            $insertId = $stmt->insert_id;
            if ($this->logLevel >= LogLevel::DEBUG) {
                $this->AddLog(__FUNCTION__ . "_raw", sprintf("DB Rows inserted : %s [InsertID: %s]", $insertedDbRows, $insertId), 0);
            }
            if ($insertedDbRows < 1) {
                if ($this->logLevel >= LogLevel::WARN) {
                    $this->AddLog(__FUNCTION__ . "_raw", sprintf("WARN 'affected_rows' inserted : %s [InsertID: %s]", $insertedDbRows, $insertId), 0);
                }
                if ($this->logLevel >= LogLevel::WARN) {
                    $this->AddLog(__FUNCTION__ . "_raw", sprintf("  @%s", $note), 0);
                }
                if ($this->logLevel >= LogLevel::WARN) {
                    $this->AddLog(__FUNCTION__ . "_raw", sprintf("  @%s", print_r($dataArr, true)), 0);
                }
            }
        } catch (Exception $e) {
            $errorMsg = sprintf("ERROR in Line '%s' > %s", $e->getLine(), $e->getMessage());
            if ($this->logLevel >= LogLevel::ERROR) {
                $this->AddLog(__FUNCTION__ . "_raw", $errorMsg, 0, true);
            }
        } finally {
            $mysqli->close();
            $mysqli = null;
        }

        return $insertedDbRows;
    }
}
