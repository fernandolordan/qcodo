<?php
	/**
	 * Codegen Qcodo CLI file
	 * Part of the Qcodo Development Framework
	 * Copyright (c) 2005-2010, Quasidea Development, LLC
	 */

	//execution timer
	$intStartTime = microtime();
	$intStartTime = explode(' ', $intStartTime);
	$intStartTime = $intStartTime[1] + $intStartTime[0];

	// Setup the Parameters for codegen
	$objParameters = new QCliParameterProcessor('codegen', 'Qcodo Code Generator v' . QCODO_VERSION);

	// Optional Parameters for Path to Codegen Settings
	$strDefaultPath = __DEVTOOLS_CLI__ . '/settings/codegen.xml';

	// Small cleanup on the text
	$strDefaultPath = str_replace('/html/../', '/', $strDefaultPath);
	$strDefaultPath = str_replace('/docroot/../', '/', $strDefaultPath);
	$strDefaultPath = str_replace('/wwwroot/../', '/', $strDefaultPath);
	$strDefaultPath = str_replace('/www/../', '/', $strDefaultPath);

	$objParameters->AddNamedParameter('s', 'settings-path', QCliParameterType::Path, $strDefaultPath, 'path to the Codegen Settings XML file; defaults to ' . $strDefaultPath);
	$objParameters->Run();

	// Pull the Parameter Values
	$strSettingsXmlPath = $objParameters->GetValue('s');

	try {
		/////////////////////
		// Run Code Gen	
		QCodeGen::Run($strSettingsXmlPath);
		/////////////////////

		if ($strErrors = QCodeGen::$RootErrors) {
			printf("The following ROOT ERRORS were reported:\r\n%s\r\n\r\n", $strErrors);
		} else {
			printf("CodeGen settings (as evaluted from %s):\r\n%s\r\n\r\n", $_SERVER['argv'][1], QCodeGen::GetSettingsXml());
		}

		foreach (QCodeGen::$CodeGenArray as $objCodeGen) {
			printf("%s\r\n---------------------------------------------------------------------\r\n", $objCodeGen->GetTitle());
			printf("%s\r\n", $objCodeGen->GetReportLabel());
			printf("%s\r\n", $objCodeGen->GenerateAll());
			if ($strErrors = $objCodeGen->Errors)
				printf("The following errors were reported:\r\n%s\r\n", $strErrors);
			print("\r\n");
		}

		foreach (QCodeGen::GenerateAggregate() as $strMessage) {
			printf("%s\r\n\r\n", $strMessage);
		}
	} catch (Exception $objExc) {
		print 'error: ' . trim($objExc->getMessage()) . "\r\n";
		exit(1);
	}

	$intEndTime = microtime();
	$intEndTime = explode(" ", $intEndTime);
	$intEndTime = $intEndTime[1] + $intEndTime[0];

	echo 'Codegen took ', round($intEndTime - $intStartTime, 2), "s (";
	if (ini_get('max_execution_time') == 0 )
		echo 'no execution time limit';
	else
		echo 'maximum execution time ', ini_get('max_execution_time'), 's';
	echo ").\n";
	echo 'Peak memory usage was ', QString::GetByteSize(memory_get_peak_usage(true)), ' (', ini_get('memory_limit'), " maximum available).\n";
?>