<?php
/*
 * Copyright (c)  2009, Tracmor, LLC
 *
 * This file is part of Tracmor.
 *
 * Tracmor is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tracmor is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tracmor; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

	// Include prepend.inc to load Qcodo
	require('../includes/prepend.inc.php');		/* if you DO NOT have "includes/" in your include_path */
	require(__DOCROOT__ . __PHP_ASSETS__ . '/csv/DataSource.php');
	QApplication::Authenticate();

	class AdminLabelsForm extends QForm {
		// Header Menu
		protected $ctlHeaderMenu;

		protected $pnlMain;
		protected $pnlStepOne;
		protected $pnlStepTwo;
		protected $pnlStepThree;
		protected $lstFieldSeparator;
		protected $txtFieldSeparator;
    protected $lstTextDelimiter;
		protected $txtTextDelimiter;
		protected $flcFileCsv;
		protected $FileCsvData;
		protected $arrCsvHeader;
		protected $arrMapFields;
		protected $strFilePathArray;
		protected $lstMapHeaderArray;
		protected $txtMapDefaultValueArray;
		protected $lstMapDefaultValueArray;
		protected $dtpDateArray;
		protected $strAcceptibleMimeArray;
		protected $chkHeaderRow;
		protected $blnHeaderRow;
		protected $btnNext;
		protected $btnCancel;
		protected $intStep;
		protected $arrAssetCustomField;
		protected $arrAssetModelCustomField;
		protected $arrTracmorField;
		protected $dtgCategory;
		protected $objNewCategoryArray;
		protected $dtgManufacturer;
		protected $objNewManufacturerArray;
		protected $dtgLocation;
		protected $objNewLocationArray;
		protected $dtgAssetModel;
		protected $objNewAssetModelArray;
		protected $dtgAsset;
		protected $objNewAssetArray;
		protected $blnImportEnd;
		protected $intImportStep;
		protected $intLocationKey;
    protected $intCategoryKey;
    protected $intManufacturerKey;
    protected $intCreatedByKey;
    protected $intCreatedDateKey;
    protected $intModifiedByKey;
    protected $intModifiedDateKey;
    protected $intUserArray;
    protected $lblImportSuccess;
    protected $intSkippedRecordCount;
    protected $strFilePath;
    protected $btnUndoLastImport;
    protected $btnImportMore;
    protected $btnReturnToAssets;
    protected $objDatabase;
    protected $btnRemoveArray;
    protected $intTotalCount;
    protected $intCurrentFile;

		protected function Form_Create() {
			// Create the Header Menu
			$this->ctlHeaderMenu_Create();
			$this->pnlMain_Create();
			$this->pnlStepOne_Create();
			$this->Buttons_Create();
			$this->intStep = 1;
			$this->intSkippedRecordCount = 0;
			$this->blnImportEnd = true;
			$this->btnRemoveArray = array();
			$this->arrAssetCustomField = array();
			$this->objDatabase = Asset::GetDatabase();
			// Load Asset Custom Field
			foreach (CustomField::LoadArrayByActiveFlagEntity(1, 1) as $objCustomField) {
			  $this->arrAssetCustomField[$objCustomField->CustomFieldId] = $objCustomField;
			}
			$this->arrAssetModelCustomField = array();
			// Load Asset Model Custom Field
			foreach (CustomField::LoadArrayByActiveFlagEntity(1, 4) as $objCustomField) {
			  $this->arrAssetModelCustomField[$objCustomField->CustomFieldId] = $objCustomField;
			}
			$this->intUserArray = array();
			// Load Users
			foreach (UserAccount::LoadAll() as $objUser) {
			  $this->intUserArray[strtolower($objUser->Username)] = $objUser->UserAccountId;
			}
			$this->strAcceptibleMimeArray = array(
						'text/plain' => 'txt',
						'text/comma-separated-values' => 'csv',
  				  'application/vnd.ms-excel' => 'csv');
		}

		// Create and Setup the Header Composite Control
		protected function ctlHeaderMenu_Create() {
			$this->ctlHeaderMenu = new QHeaderMenu($this);
		}

		// Main Panel
		protected function pnlMain_Create() {
		  $this->pnlMain = new QPanel($this);
		  $this->pnlMain->AutoRenderChildren = true;
		}

		// Step 1 Panel
		protected function pnlStepOne_Create() {
			$this->pnlStepOne = new QPanel($this->pnlMain);
      $this->pnlStepOne->Template = "asset_import_pnl_step1.tpl.php";

			// Step 1
			$this->lstFieldSeparator = new QRadioButtonList($this->pnlStepOne);
			$this->lstFieldSeparator->Name = "Field Separator: ";
			$this->lstFieldSeparator->Width = 200;
			$this->lstFieldSeparator->AddItem(new QListItem('Comma Separated', 1));
			$this->lstFieldSeparator->AddItem(new QListItem('Tab Separated', 2));
			$this->lstFieldSeparator->AddItem(new QListItem('Other', 'other'));
			$this->lstFieldSeparator->SelectedIndex = 0;
			$this->lstFieldSeparator->AddAction(new QChangeEvent(), new QAjaxAction('lstFieldSeparator_Change'));
			$this->txtFieldSeparator = new QTextBox($this->pnlStepOne);
			$this->txtFieldSeparator->Width = 100;
			$this->txtFieldSeparator->Display = false;
			$this->lstTextDelimiter = new QListBox($this->pnlStepOne);
			$this->lstTextDelimiter->Name = "Text Delimiter: ";
			$this->lstTextDelimiter->Width = 150;
			$this->lstTextDelimiter->AddItem(new QListItem('None', 1));
			$this->lstTextDelimiter->AddItem(new QListItem('Single Quote (\')', 2));
			$this->lstTextDelimiter->AddItem(new QListItem('Double Quote (")', 3));
			$this->lstTextDelimiter->AddItem(new QListItem('Other', 'other'));
			$this->lstTextDelimiter->AddAction(new QChangeEvent(), new QAjaxAction('lstTextDelimiter_Change'));
			$this->txtTextDelimiter = new QTextBox($this->pnlStepOne);
			$this->txtTextDelimiter->Width = 100;
			$this->txtTextDelimiter->Display = false;
			$this->flcFileCsv = new QFileControlExt($this->pnlStepOne);
			$this->flcFileCsv->Name = "Select File: ";
			$this->chkHeaderRow = new QCheckBox($this->pnlStepOne);
			$this->chkHeaderRow->Name = "Header Row: ";
    }


    // Step 2 Panel
    protected function pnlStepTwo_Create() {
			$this->pnlStepTwo = new QPanel($this->pnlMain);
      $this->pnlStepTwo->Template = "asset_import_pnl_step2.tpl.php";
    }

    // Step 3 Panel
    protected function pnlStepThree_Create() {
			$this->pnlStepThree = new QPanel($this->pnlMain);
      $this->pnlStepThree->Template = "asset_import_pnl_step3.tpl.php";
    }

    protected function Buttons_Create() {
      // Buttons
			$this->btnNext = new QButton($this);
			$this->btnNext->Text = "Next";
			$this->btnNext->AddAction(new QClickEvent(), new QServerAction('btnNext_Click'));
			$this->btnNext->AddAction(new QEnterKeyEvent(), new QServerAction('btnNext_Click'));
			$this->btnNext->AddAction(new QEnterKeyEvent(), new QTerminateAction());
			$this->btnCancel = new QButton($this);
			$this->btnCancel->Text = "Cancel";
			$this->btnCancel->AddAction(new QClickEvent(), new QAjaxAction('btnCancel_Click'));
			$this->btnCancel->AddAction(new QEnterKeyEvent(), new QAjaxAction('btnCancel_Click'));
			$this->btnCancel->AddAction(new QEnterKeyEvent(), new QTerminateAction());
    }

		protected function lstFieldSeparator_Change() {
		  switch ($this->lstFieldSeparator->SelectedValue) {
		    case 'other':
		      $this->txtFieldSeparator->Display = true;
		      break;
		    default:
		      $this->txtFieldSeparator->Display = false;
		  }
		}

		protected function lstTextDelimiter_Change() {
      switch ($this->lstTextDelimiter->SelectedValue) {
		    case 'other':
		      $this->txtTextDelimiter->Display = true;
		      break;
		    default:
		      $this->txtTextDelimiter->Display = false;
		  }
		}

		// Next button click action
		protected function btnNext_Click() {
		  $blnError = false;
		  if ($this->intStep == 1) {
		    if ($this->chkHeaderRow->Checked) {
		      $this->blnHeaderRow = true;
		    }
		    else {
		      $this->blnHeaderRow = false;
		    }
		    // Check errors
		    if ($this->lstFieldSeparator->SelectedValue == 'other' && !$this->txtFieldSeparator->Text) {
		      $this->flcFileCsv->Warning = "Please enter the field separator.";
		      $blnError = true;
		    }
		    elseif ($this->lstTextDelimiter->SelectedValue == 'other' && !$this->txtTextDelimiter->Text) {
		      $this->flcFileCsv->Warning = "Please enter the text delimiter.";
		      $blnError = true;
		    }
		    else {
  		    // Step 1 complete
          // File Not Uploaded
    			if (!file_exists($this->flcFileCsv->File) || !$this->flcFileCsv->Size) {
    				throw new QCallerException('FileAssetType must be a valid QFileAssetType constant value');
    			// File Has Incorrect MIME Type (only if an acceptiblemimearray is setup)
    			} elseif (is_array($this->strAcceptibleMimeArray) && (!array_key_exists($this->flcFileCsv->Type, $this->strAcceptibleMimeArray))) {
    				$this->flcFileCsv->Warning = "Extension must be 'csv' or 'txt'";
    				$blnError = true;
    			// File Successfully Uploaded
    			} else {
    			  $this->flcFileCsv->Warning = "";
    				// Setup Filename, Base Filename and Extension
    				$strFilename = $this->flcFileCsv->FileName;
    				$intPosition = strrpos($strFilename, '.');
    			}
    			if (!$blnError) {
    			  $this->FileCsvData = new File_CSV_DataSource();
    			  // Setup the settings which have got on step 1
    			  $this->FileCsvData->settings($this->GetCsvSettings());
    			  $file = fopen($this->flcFileCsv->File, "r");
            // Counter of files
            $i=1;
            // Counter of rows
            $j=1;
            $this->strFilePathArray = array();
            // The uploaded file splits up in order to avoid out of memory
            while ($row = fgets($file, 1000)) {
              if ($j == 1) {
                $strFilePath = sprintf('%s/%s_%s.csv', __DOCROOT__ . __SUBDIRECTORY__ . __TRACMOR_TMP__, $_SESSION['intUserAccountId'], $i);
                $this->strFilePathArray[] = $strFilePath;
                $file_part = fopen($strFilePath, "w+");
              }

              fwrite($file_part, $row);
              $j++;
              if ($j > 200) {
                $j = 1;
                $i++;
                fclose($file_part);
              }
            }
            $this->intTotalCount = $i*200 + $j-1;
            if (QApplication::$TracmorSettings->AssetLimit != null && QApplication::$TracmorSettings->AssetLimit < ($this->intTotalCount + Asset::CountAll())) {
              $blnError = true;
              $this->btnNext->Warning = "You can not import too many assets. You have the asset limit = " . ($this->intTotalCount + Asset::CountAll());
            }
            else {
              $this->arrMapFields = array();
              $this->arrTracmorField = array();
              // Load first file
              $this->FileCsvData->load($this->strFilePathArray[0]);
              // Get Headers
              if ($this->blnHeaderRow) {
                $this->arrCsvHeader = $this->FileCsvData->getHeaders();
              }
              else {
                // If it is not first file
                $this->FileCsvData->appendRow($this->FileCsvData->getHeaders());
              }
              $file_skipped = fopen($this->strFilePath = sprintf('%s/%s_skipped.csv', __DOCROOT__ . __SUBDIRECTORY__ . __TRACMOR_TMP__, $_SESSION['intUserAccountId']), "w+");
              $strFirstRowArray = $this->FileCsvData->getRow(0);
              for ($i=0; $i<count($strFirstRowArray); $i++) {
                $this->arrMapFields[$i] = array();
                if ($this->blnHeaderRow) {
                  $this->lstMapHeader_Create($this, $i, $this->arrCsvHeader[$i]);
                  $this->arrMapFields[$i]['header'] = $this->arrCsvHeader[$i];
                  // Create the header row in the skipped error file
                  $this->PutSkippedRecordInFile($file_skipped, $strFirstRowArray);
                }
                else {
                  $this->lstMapHeader_Create($this, $i);
                }
                // Create Default Value TextBox, ListBox and DateTimePicker
                if ($this->blnHeaderRow && $this->arrCsvHeader[$i] || !$this->blnHeaderRow) {
                  $txtDefaultValue = new QTextBox($this);
                  $txtDefaultValue->Width = 200;
                  $this->txtMapDefaultValueArray[] = $txtDefaultValue;

                  $lstDefaultValue = new QListBox($this);
                  $lstDefaultValue->Width = 200;
                  $lstDefaultValue->Display = false;
                  $this->lstMapDefaultValueArray[] = $lstDefaultValue;

                  $dtpDate = new QDateTimePicker($this);
                	$dtpDate->DateTimePickerType = QDateTimePickerType::Date;
                	$dtpDate->DateTimePickerFormat = QDateTimePickerFormat::MonthDayYear;
                	$dtpDate->Display = false;
                	$this->dtpDateArray[] = $dtpDate;
                }
                $this->arrMapFields[$i]['row1'] = $strFirstRowArray[$i];
              }
              $this->btnNext->Text = "Import Now";
              fclose($file_skipped);
              // Create Add Fiekd button
              $btnAddField = new QButton($this);
              $btnAddField->Text = "Add Field";
              $btnAddField->AddAction(new QClickEvent(), new QServerAction('btnAddField_Click'));
              $btnAddField->AddAction(new QEnterKeyEvent(), new QServerAction('btnAddField_Click'));
              $btnAddField->AddAction(new QEnterKeyEvent(), new QTerminateAction());
              $this->lstMapHeaderArray[] = $btnAddField;
            }
    			}
		    }
		  }
		  elseif ($this->intStep == 2) {
		    // Step 2 complete
		    $blnError = false;
        $blnAssetCode = false;
        $blnLocation = false;
        $blnAssetModelCode = false;
        $blnAssetModelShortDescription = false;
        $blnCategory = false;
        $blnManufacturer = false;
        // Checking errors (Location, Asset Code, Model Short Description, Model Code, Category and Manufacturer must be selected)
        for ($i=0; $i < count($this->lstMapHeaderArray)-1; $i++) {
          $lstMapHeader = $this->lstMapHeaderArray[$i];
          $strSelectedValue = strtolower($lstMapHeader->SelectedValue);
          if ($strSelectedValue == "location") {
            $blnLocation = true;
          }
          elseif ($strSelectedValue == "asset code") {
            $blnAssetCode = true;
          }
          elseif ($strSelectedValue == "asset model short description") {
            $blnAssetModelShortDescription = true;
          }
          elseif ($strSelectedValue == "asset model code") {
            $blnAssetModelCode = true;
          }
          elseif ($strSelectedValue == "category") {
            $blnCategory = true;
          }
          elseif ($strSelectedValue == "manufacturer") {
            $blnManufacturer = true;
          }
        }
        if ($this->lstMapDefaultValueArray) {
          // Checking errors for required Default Value text fields
          foreach ($this->lstMapDefaultValueArray as $lstDefault) {
            if ($lstDefault->Display && $lstDefault->Required && !$lstDefault->SelectedValue) {
              $lstDefault->Warning = "You must select one default value.";
              $blnError = true;
              break;
            }
            else {
              $blnError = false;
              $lstDefault->Warning = "";
            }
          }
        }
        if ($this->txtMapDefaultValueArray) {
          // Checking errors for required Default Value lst fields
          foreach ($this->txtMapDefaultValueArray as $txtDefault) {
            if ($txtDefault->Display && $txtDefault->Required && !$txtDefault->Text) {
              $txtDefault->Warning = "You must enter default value.";
              break;
            }
            else {
              $blnError = false;
              $txtDefault->Warning = "";
            }
          }
        }
        // If all required fields have no errors
        if (!$blnError && $blnAssetCode && $blnAssetModelCode && $blnAssetModelShortDescription && $blnLocation && $blnCategory && $blnManufacturer) {
          $this->btnNext->Warning = "";
          // Setup keys for main required fields
          foreach ($this->arrTracmorField as $key => $value) {
            if ($value == 'location') {
              $this->intLocationKey = $key;
            }
            elseif ($value == 'category') {
              $this->intCategoryKey = $key;
            }
            elseif ($value == 'manufacturer') {
              $this->intManufacturerKey = $key;
            }
            elseif ($value == 'created by') {
              $this->intCreatedByKey = $key;
            }
            elseif ($value == 'created date') {
              $this->intCreatedDateKey = $key;
            }
            elseif ($value == 'modified by') {
              $this->intModifiedByKey = $key;
            }
            elseif ($value == 'modified date') {
              $this->intModifiedDateKey = $key;
            }
          }

          $strLocationArray = array();
          // Load all locations
          foreach (Location::LoadAll() as $objLocation) {
            $strLocationArray[] = stripslashes($objLocation->ShortDescription);
          }
          $txtDefaultValue = trim($this->txtMapDefaultValueArray[$this->intLocationKey]->Text);
          // Add default value in database if it is not exist
          if ($txtDefaultValue && !$this->in_array_nocase($txtDefaultValue, $strLocationArray)) {
            $strLocationArray[] = $txtDefaultValue;
            $objNewLocation = new Location();
            $objNewLocation->ShortDescription = addslashes($txtDefaultValue);
            $objNewLocation->Save();
            $this->objNewLocationArray[$objNewLocation->LocationId] = $objNewLocation->ShortDescription;
          }
          $this->objNewLocationArray = array();
          $this->objNewCategoryArray = array();
          $this->objNewManufacturerArray = array();
          $this->objNewAssetModelArray = array();
          $this->blnImportEnd = false;
          $j=1;
          // Add all unique locations in database
          foreach ($this->strFilePathArray as $strFilePath) {
            $this->FileCsvData->load($strFilePath);
            if ($j != 1) {
              $this->FileCsvData->appendRow($this->FileCsvData->getHeaders());
            }
            // Location Import
            for ($i=0; $i<$this->FileCsvData->countRows(); $i++) {
              $strRowArray = $this->FileCsvData->getRow($i);
              if (trim($strRowArray[$this->intLocationKey]) && !$this->in_array_nocase(trim($strRowArray[$this->intLocationKey]), $strLocationArray)) {
                $strLocationArray[] = trim($strRowArray[$this->intLocationKey]);
                $objNewLocation = new Location();
                $objNewLocation->ShortDescription = addslashes(trim($strRowArray[$this->intLocationKey]));
                $objNewLocation->Save();
                $this->objNewLocationArray[$objNewLocation->LocationId] = $objNewLocation->ShortDescription;
              }
            }
            $j++;
          }
          $this->btnNext->RemoveAllActions('onclick');
          // Add new ajax actions for button
          $this->btnNext->AddAction(new QClickEvent(), new QAjaxAction('btnNext_Click'));
          $this->btnNext->AddAction(new QClickEvent(), new QToggleEnableAction($this->btnNext));
    			$this->btnNext->AddAction(new QEnterKeyEvent(), new QAjaxAction('btnNext_Click'));
    			$this->btnNext->AddAction(new QEnterKeyEvent(), new QToggleEnableAction($this->btnNext));
    			$this->btnNext->AddAction(new QEnterKeyEvent(), new QTerminateAction());
          $this->btnNext->Warning = "Locations have been imported. Please wait...";
          $this->intImportStep = 2;
          $this->intCurrentFile = 0;

          // New locations
          $this->dtgLocation = new QDataGrid($this);
          $this->dtgLocation->Name = 'location_list';
      		$this->dtgLocation->CellPadding = 5;
      		$this->dtgLocation->CellSpacing = 0;
      		$this->dtgLocation->CssClass = "datagrid";
          $this->dtgLocation->UseAjax = true;
          $this->dtgLocation->ShowColumnToggle = false;
          $this->dtgLocation->ShowExportCsv = false;
          $this->dtgLocation->ShowHeader = false;
          $this->dtgLocation->AddColumn(new QDataGridColumnExt('Location', '<?= $_ITEM ?>', 'CssClass="dtg_column"', 'HtmlEntities="false"'));

          // New categories
          $this->dtgCategory = new QDataGrid($this);
          $this->dtgCategory->Name = 'category_list';
      		$this->dtgCategory->CellPadding = 5;
      		$this->dtgCategory->CellSpacing = 0;
      		$this->dtgCategory->CssClass = "datagrid";
          $this->dtgCategory->UseAjax = true;
          $this->dtgCategory->ShowColumnToggle = false;
          $this->dtgCategory->ShowExportCsv = false;
          $this->dtgCategory->ShowHeader = false;
          $this->dtgCategory->AddColumn(new QDataGridColumnExt('Manufacturer', '<?= $_ITEM ?>', 'CssClass="dtg_column"', 'HtmlEntities="false"'));

          // New manufacturers
          $this->dtgManufacturer = new QDataGrid($this);
          $this->dtgManufacturer->Name = 'manufacturer_list';
      		$this->dtgManufacturer->CellPadding = 5;
      		$this->dtgManufacturer->CellSpacing = 0;
      		$this->dtgManufacturer->CssClass = "datagrid";
          $this->dtgManufacturer->UseAjax = true;
          $this->dtgManufacturer->ShowColumnToggle = false;
          $this->dtgManufacturer->ShowExportCsv = false;
          $this->dtgManufacturer->ShowHeader = false;
          $this->dtgManufacturer->AddColumn(new QDataGridColumnExt('Manufacturer', '<?= $_ITEM ?>', 'CssClass="dtg_column"', 'HtmlEntities="false"'));

          // New asset models
          $this->dtgAssetModel = new QDataGrid($this);
          $this->dtgAssetModel->Name = 'asset_model_list';
      		$this->dtgAssetModel->CellPadding = 5;
      		$this->dtgAssetModel->CellSpacing = 0;
      		$this->dtgAssetModel->CssClass = "datagrid";
          $this->dtgAssetModel->UseAjax = true;
          $this->dtgAssetModel->ShowColumnToggle = false;
          $this->dtgAssetModel->ShowExportCsv = false;
          $this->dtgAssetModel->ShowHeader = false;
          $this->dtgAssetModel->AddColumn(new QDataGridColumnExt('Asset Model', '<?= $_ITEM ?>', 'CssClass="dtg_column"', 'HtmlEntities="false"'));

          // New assets
          $this->dtgAsset = new QDataGrid($this);
          $this->dtgAsset->Name = 'asset_list';
      		$this->dtgAsset->CellPadding = 5;
      		$this->dtgAsset->CellSpacing = 0;
      		$this->dtgAsset->CssClass = "datagrid";
          $this->dtgAsset->UseAjax = true;
          $this->dtgAsset->ShowColumnToggle = false;
          $this->dtgAsset->ShowExportCsv = false;
          $this->dtgAsset->ShowHeader = false;
          $this->dtgAsset->AddColumn(new QDataGridColumnExt('Asset', '<?= $_ITEM ?>', 'CssClass="dtg_column"', 'HtmlEntities="false"'));

          // Create the label for successful import
          $this->lblImportSuccess = new QLabel($this);
        	$this->lblImportSuccess->HtmlEntities = false;
        	$this->lblImportSuccess->Display = false;

        	// Undo Last Import button
        	$this->btnUndoLastImport = new QButton($this);
        	$this->btnUndoLastImport->Text = "Undo Last Import";
        	$this->btnUndoLastImport->Display = false;
        	$this->btnUndoLastImport->AddAction(new QClickEvent(), new QServerAction('btnCancel_Click'));
    			$this->btnUndoLastImport->AddAction(new QEnterKeyEvent(), new QServerAction('btnCancel_Click'));
    			$this->btnUndoLastImport->AddAction(new QEnterKeyEvent(), new QTerminateAction());

    			// Import More button
    			$this->btnImportMore = new QButton($this);
        	$this->btnImportMore->Text = "Import More";
        	$this->btnImportMore->Display = false;
        	$this->btnImportMore->AddAction(new QClickEvent(), new QServerAction('btnImportMore_Click'));
    			$this->btnImportMore->AddAction(new QEnterKeyEvent(), new QServerAction('btnImportMore_Click'));
    			$this->btnImportMore->AddAction(new QEnterKeyEvent(), new QTerminateAction());

    			// Return to Assets button
    			$this->btnReturnToAssets = new QButton($this);
        	$this->btnReturnToAssets->Text = "Return to Assets";
        	$this->btnReturnToAssets->Display = false;
        	$this->btnReturnToAssets->AddAction(new QClickEvent(), new QServerAction('btnReturnToAssets_Click'));
    			$this->btnReturnToAssets->AddAction(new QEnterKeyEvent(), new QServerAction('btnReturnToAssets_Click'));
    			$this->btnReturnToAssets->AddAction(new QEnterKeyEvent(), new QTerminateAction());
        }
        else {
          $this->btnNext->Warning = "You must select all required fields (Asset Code, Asset Model Code, Asset Model Short Description, Location, Category and Manufacturer).";
          $blnError = true;
        }
		  }
		  else {
		    // Step 3 complete
		    set_time_limit(0);
		    $file_skipped = fopen($strFilePath = sprintf('%s/%s_skipped.csv', __DOCROOT__ . __SUBDIRECTORY__ . __TRACMOR_TMP__, $_SESSION['intUserAccountId']), "a");
		    if (!$this->blnImportEnd) {
		      // Category
          if ($this->intImportStep == 2) {
            $strCategoryArray = array();
            $this->objNewCategoryArray = array();
            // Load all categories
            foreach (Category::LoadAll() as $objCategory) {
              $strCategoryArray[] = stripslashes($objCategory->ShortDescription);
            }
            // Add Default value
            $txtDefaultValue = trim($this->txtMapDefaultValueArray[$this->intCategoryKey]->Text);
            if ($txtDefaultValue && !$this->in_array_nocase($txtDefaultValue, $strCategoryArray)) {
              $strCategoryArray[] = $txtDefaultValue;
              $objNewCategory = new Category();
              $objNewCategory->ShortDescription = addslashes($txtDefaultValue);
              $objNewCategory->AssetFlag = true;
              $objNewCategory->InventoryFlag = false;
              $objNewCategory->Save();
              $this->objNewCategoryArray[$objNewCategory->CategoryId] = $objNewCategory->ShortDescription;
            }
            $this->btnNext->Warning = "Categories have been imported. Please wait...";
          }
          // Manufacturer
          elseif ($this->intImportStep == 3) {
            $strManufacturerArray = array();
            $this->objNewManufacturerArray = array();
            // Load all manufacturers
            foreach (Manufacturer::LoadAll() as $objManufacturer) {
              $strManufacturerArray[] = stripslashes($objManufacturer->ShortDescription);
            }
            $txtDefaultValue = trim($this->txtMapDefaultValueArray[$this->intManufacturerKey]->Text);
            // Add Default Value
            if ($txtDefaultValue && !$this->in_array_nocase($txtDefaultValue, $strManufacturerArray)) {
              $strManufacturerArray[] = $txtDefaultValue;
              $objNewManufacturer = new Manufacturer();
              $objNewManufacturer->ShortDescription = addslashes($txtDefaultValue);
              $objNewManufacturer->Save();
              $this->objNewManufacturerArray[$objNewManufacturer->ManufacturerId] = $objNewManufacturer->ShortDescription;
            }
            $this->btnNext->Warning = "Manufacturers have been imported. Please wait...";
          }
          // Asset Model
          elseif ($this->intImportStep == 4) {
            $intCategoryArray = array();
            // Load all categories with keys=category_id
            foreach (Category::LoadAllWithFlags(true, false) as $objCategory) {
              //$intCategoryArray["'" . strtolower($objCategory->ShortDescription) . "'"] = $objCategory->CategoryId;
              $intCategoryArray[$objCategory->CategoryId] = strtolower($objCategory->ShortDescription);
            }
            $intManufacturerArray = array();
            // Load all manufacturers with keys=manufacturer_id
            foreach (Manufacturer::LoadAll() as $objManufacturer) {
              //$intManufacturerArray["'" . strtolower($objManufacturer->ShortDescription) . "'"] = $objManufacturer->ManufacturerId;
              $intManufacturerArray[$objManufacturer->ManufacturerId] = strtolower($objManufacturer->ShortDescription);
            }

            $intModelCustomFieldKeyArray = array();
            $arrAssetModelCustomField = array();
            // Setup keys
            foreach ($this->arrTracmorField as $key => $value) {
              if ($value == 'asset model short description') {
                $intModelShortDescriptionKey = $key;
              }
              elseif ($value == 'asset model long description') {
                $intModelLongDescriptionKey = $key;
              }
              elseif ($value == 'asset model code') {
                $intModelCodeKey = $key;
              }
              elseif ($value == 'asset code') {
                $intAssetCode = $key;
              }
              elseif (substr($value, 0, 6) == 'model_') {
                $intModelCustomFieldKeyArray[substr($value, 6)] = $key;
                $arrAssetModelCustomField[substr($value, 6)] = $this->arrAssetCustomField[substr($value, 6)];
              }
            }
            $strAssetModelArray = array();
            // Load all asset models
            foreach (AssetModel::LoadAll() as $objAssetModel) {
              $strAssetModelArray[] = strtolower(sprintf("%s_%s_%s", $objAssetModel->ShortDescription, $objAssetModel->CategoryId, $objAssetModel->ManufacturerId));
            }
            $this->btnNext->Warning = sprintf("Please wait... Asset Model import complete: %s%s", ceil($this->intCurrentFile*200/$this->intTotalCount*100), "%");
          }
          // Asset
          elseif ($this->intImportStep == 5) {
            $intCategoryArray = array();
            // Load all categories with keys=category_id
            foreach (Category::LoadAllWithFlags(true, false) as $objCategory) {
              //$intCategoryArray["'" . strtolower($objCategory->ShortDescription) . "'"] = $objCategory->CategoryId;
              $intCategoryArray[$objCategory->CategoryId] = strtolower($objCategory->ShortDescription);
            }
            $intManufacturerArray = array();
            // Load all manufacturers with keys=manufacturer_id
            foreach (Manufacturer::LoadAll() as $objManufacturer) {
              //$intManufacturerArray["'" . strtolower($objManufacturer->ShortDescription) . "'"] = $objManufacturer->ManufacturerId;
              $intManufacturerArray[$objManufacturer->ManufacturerId] = strtolower($objManufacturer->ShortDescription);
            }
            $intAssetModelArray = array();
            // Load all asset models with keys=asset_model_id
            foreach (AssetModel::LoadAll() as $objAssetModel) {
              //$intAssetModelArray["'" . strtolower($objAssetModel->ShortDescription) . "'"] = $objAssetModel->AssetModelId;
              $intAssetModelArray[$objAssetModel->AssetModelId] = strtolower(sprintf("%s_%s_%s_%s", $objAssetModel->AssetModelCode, $objAssetModel->ShortDescription, $objAssetModel->CategoryId, $objAssetModel->ManufacturerId));
            }
            $intAssetCustomFieldKeyArray = array();
            $arrAssetCustomField = array();
            // Setup keys
            foreach ($this->arrTracmorField as $key => $value) {
              if ($value == 'asset model short description') {
                $intModelShortDescriptionKey = $key;
              }
              elseif ($value == 'asset model code') {
                $intModelCodeKey = $key;
              }
              elseif ($value == 'asset code') {
                $intAssetCode = $key;
              }
              elseif (substr($value, 0, 6) == 'asset_') {
                $intAssetCustomFieldKeyArray[substr($value, 6)] = $key;
                $arrAssetCustomField[substr($value, 6)] = $this->arrAssetCustomField[substr($value, 6)];
              }
            }
            $intLocationArray = array();
            // Load all locations with keys=location_id
            foreach (Location::LoadAll() as $objLocation) {
              //$intLocationArray["'" . strtolower($objLocation->ShortDescription) . "'"] = $objLocation->LocationId;
              $intLocationArray[$objLocation->LocationId] = strtolower($objLocation->ShortDescription);
            }

            $strAssetArray = array();
            // Load all assets
            foreach (Asset::LoadAll() as $objAsset) {
              $strAssetArray[] = strtolower($objAsset->AssetCode);
            }
            $this->btnNext->Warning = sprintf("Please wait... Asset import complete: %s%s", ceil($this->intCurrentFile*200/$this->intTotalCount*100), "%");
          }

          for ($j=$this->intCurrentFile; $j<count($this->strFilePathArray); $j++) {
            $this->FileCsvData->load($this->strFilePathArray[$j]);
            if (!$j) {
              $this->FileCsvData->appendRow($this->FileCsvData->getHeaders());
            }
            // Category Import
            if ($this->intImportStep == 2) {
              for ($i=0; $i<$this->FileCsvData->countRows(); $i++) {
                $strRowArray = $this->FileCsvData->getRow($i);
                if (trim($strRowArray[$this->intCategoryKey]) && !$this->in_array_nocase(trim($strRowArray[$this->intCategoryKey]), $strCategoryArray)) {
                  $strCategoryArray[] = trim($strRowArray[$this->intCategoryKey]);
                  $objNewCategory = new Category();
                  $objNewCategory->ShortDescription = addslashes(trim($strRowArray[$this->intCategoryKey]));
                  $objNewCategory->AssetFlag = true;
                  $objNewCategory->InventoryFlag = false;
                  $objNewCategory->Save();
                  $this->objNewCategoryArray[$objNewCategory->CategoryId] = $objNewCategory->ShortDescription;
                }
              }
            }
            // Manufacturer Import
            elseif ($this->intImportStep == 3) {
              for ($i=0; $i<$this->FileCsvData->countRows(); $i++) {
                $strRowArray = $this->FileCsvData->getRow($i);
                if (trim($strRowArray[$this->intManufacturerKey]) && !$this->in_array_nocase(trim($strRowArray[$this->intManufacturerKey]), $strManufacturerArray)) {
                  $strManufacturerArray[] = trim($strRowArray[$this->intManufacturerKey]);
                  $objNewManufacturer = new Manufacturer();
                  $objNewManufacturer->ShortDescription = addslashes(trim($strRowArray[$this->intManufacturerKey]));
                  $objNewManufacturer->Save();
                  $this->objNewManufacturerArray[$objNewManufacturer->ManufacturerId] = $objNewManufacturer->ShortDescription;
                }
              }
            }
            // Asset Model import
            elseif ($this->intImportStep == 4) {
              for ($i=0; $i<$this->FileCsvData->countRows(); $i++) {
                $strRowArray = $this->FileCsvData->getRow($i);
                $strShortDescription = (trim($strRowArray[$intModelShortDescriptionKey])) ? addslashes(trim($strRowArray[$intModelShortDescriptionKey])) : false;
                $strAssetModelCode = trim($strRowArray[$intModelCodeKey]) ? addslashes(trim($strRowArray[$intModelCodeKey])) : addslashes(trim($this->txtMapDefaultValueArray[$intModelCodeKey]->Text));
                $strKeyArray = array_keys($intCategoryArray, addslashes(strtolower(trim($strRowArray[$this->intCategoryKey]))));
                if (count($strKeyArray)) {
                  $intCategoryId = $strKeyArray[0];
                }
                else {
                  $strKeyArray = array_keys($intCategoryArray, addslashes(strtolower(trim($this->txtMapDefaultValueArray[$this->intCategoryKey]->Text))));
                  if (count($strKeyArray)) {
                    $intCategoryId = $strKeyArray[0];
                  }
                  else {
                    $intCategoryId = false;
                  }
                }
                $strKeyArray = array_keys($intManufacturerArray, addslashes(strtolower(trim($strRowArray[$this->intManufacturerKey]))));
                if (count($strKeyArray)) {
                  $intManufacturerId = $strKeyArray[0];
                }
                else {
                  $strKeyArray = array_keys($intManufacturerArray, addslashes(strtolower(trim($this->txtMapDefaultValueArray[$this->intManufacturerKey]->Text))));
                  if (count($strKeyArray)) {
                    $intManufacturerId = $strKeyArray[0];
                  }
                  else {
                    $intManufacturerId = false;
                  }
                }
                if (!$strShortDescription || $intCategoryId === false || $intManufacturerId === false) {
                  //$blnError = true;
                  //$this->intSkippedRecordCount++;
                  //$this->PutSkippedRecordInFile($file_skipped, $strRowArray);
                  //break;
                  $strAssetModel =  null;
                }
                else {
                  //$blnError = false;
                  $strAssetModel = strtolower(sprintf("%s_%s_%s", $strShortDescription, $intCategoryId, $intManufacturerId));
                }
                if ($strAssetModel && !$this->in_array_nocase($strAssetModel, $strAssetModelArray)) {
                  $strAssetModelArray[] = $strAssetModel;
                  $objNewAssetModel = new AssetModel();
                  $objNewAssetModel->ShortDescription = $strShortDescription;
                  $objNewAssetModel->AssetModelCode = $strAssetModelCode;
                  $objNewAssetModel->CategoryId = $intCategoryId;
                  $objNewAssetModel->ManufacturerId = $intManufacturerId;

                  if (isset($intModelLongDescriptionKey)) {
                    $objNewAssetModel->LongDescription = addslashes(trim($strRowArray[$intModelLongDescriptionKey]));
                  }
                  $objNewAssetModel->Save();
                  // Asset Model Custom Field import
                  foreach ($arrAssetModelCustomField as $objCustomField) {
                    if ($objCustomField->CustomFieldQtypeId != 2) {
                      $objCustomField->CustomFieldSelection = new CustomFieldSelection;
          						$objCustomField->CustomFieldSelection->newCustomFieldValue = new CustomFieldValue;
          						$objCustomField->CustomFieldSelection->newCustomFieldValue->CustomFieldId = $objCustomField->CustomFieldId;
          						if (trim($strRowArray[$intModelCustomFieldKeyArray[$objCustomField->CustomFieldId]])) {
          						  $objCustomField->CustomFieldSelection->newCustomFieldValue->ShortDescription = addslashes(trim($strRowArray[$intModelCustomFieldKeyArray[$objCustomField->CustomFieldId]]));
          						}
          						else {
          						  $objCustomField->CustomFieldSelection->newCustomFieldValue->ShortDescription = addslashes($this->txtMapDefaultValueArray[$intModelCustomFieldKeyArray[$objCustomField->CustomFieldId]]->Text);
          						}
          						$objCustomField->CustomFieldSelection->newCustomFieldValue->Save();
          						$objCustomField->CustomFieldSelection->EntityId = $objNewAssetModel->AssetModelId;
          						$objCustomField->CustomFieldSelection->EntityQtypeId = 4;
          						$objCustomField->CustomFieldSelection->CustomFieldValueId = $objCustomField->CustomFieldSelection->newCustomFieldValue->CustomFieldValueId;
          						$objCustomField->CustomFieldSelection->Save();
                    }
                    else {
                      $data = trim($strRowArray[$intModelCustomFieldKeyArray[$objCustomField->CustomFieldId]]);
                      $blnInList = false;
                      $objCustomField->CustomFieldSelection = new CustomFieldSelection;
                			$objCustomField->CustomFieldSelection->EntityId = $objNewAssetModel->AssetModelId;
                			$objCustomField->CustomFieldSelection->EntityQtypeId = 4;
                			foreach (CustomFieldValue::LoadArrayByCustomFieldId($objCustomField->CustomFieldId) as $objCustomFieldValue) {
            					  if (strtolower($objCustomFieldValue->ShortDescription) == $data) {
                 					$objCustomField->CustomFieldSelection->CustomFieldValueId = $objCustomFieldValue->CustomFieldValueId;
                					$blnInList = true;
                					break;
            					  }
            					}
            					if (!$blnInList && $this->lstMapDefaultValueArray[$intModelCustomFieldKeyArray[$objCustomField->CustomFieldId]]->SelectedValue != null) {
             					  $objCustomField->CustomFieldSelection->CustomFieldValueId = $this->lstMapDefaultValueArray[$intModelCustomFieldKeyArray[$objCustomField->CustomFieldId]]->SelectedValue;
             					  $objCustomField->CustomFieldSelection->Save();
            					}
            					elseif ($data) {
            					  $objCustomField->CustomFieldSelection->Save();
            					}
                    }
                  }
                  $this->objNewAssetModelArray[$objNewAssetModel->AssetModelId] = $objNewAssetModel->ShortDescription;
                }
                $this->intCurrentFile++;
                break;
              }
            }
            // Asset import
            elseif ($this->intImportStep == 5) {
              for ($i=0; $i<$this->FileCsvData->countRows(); $i++) {
                $strRowArray = $this->FileCsvData->getRow($i);
                $strShortDescription = (trim($strRowArray[$intModelShortDescriptionKey])) ? addslashes(trim($strRowArray[$intModelShortDescriptionKey])) : false;
                $strAssetModelCode = trim($strRowArray[$intModelCodeKey]) ? addslashes(trim($strRowArray[$intModelCodeKey])) : addslashes(trim($this->txtMapDefaultValueArray[$intModelCodeKey]->Text));
                $strKeyArray = array_keys($intCategoryArray, addslashes(strtolower(trim($strRowArray[$this->intCategoryKey]))));
                if (count($strKeyArray)) {
                  $intCategoryId = $strKeyArray[0];
                }
                else {
                  $strKeyArray = array_keys($intCategoryArray, addslashes(strtolower(trim($this->txtMapDefaultValueArray[$this->intCategoryKey]->Text))));
                  if (count($strKeyArray)) {
                    $intCategoryId = $strKeyArray[0];
                  }
                  else {
                    $intCategoryId = false;
                  }
                }
                $strKeyArray = array_keys($intManufacturerArray, addslashes(strtolower(trim($strRowArray[$this->intManufacturerKey]))));
                if (count($strKeyArray)) {
                  $intManufacturerId = $strKeyArray[0];
                }
                else {
                  $strKeyArray = array_keys($intManufacturerArray, addslashes(strtolower(trim($this->txtMapDefaultValueArray[$this->intManufacturerKey]->Text))));
                  if (count($strKeyArray)) {
                    $intManufacturerId = $strKeyArray[0];
                  }
                  else {
                    $intManufacturerId = false;
                  }
                }
                if (!$strShortDescription || $intCategoryId === false || $intManufacturerId === false) {
                  //$blnError = true;
                  $this->intSkippedRecordCount++;
                  $this->PutSkippedRecordInFile($file_skipped, $strRowArray);
                  //break;
                }
                else {
                  //$blnError = false;
                  $strAssetModel = strtolower(sprintf("%s_%s_%s_%s", $strAssetModelCode, $strShortDescription, $intCategoryId, $intManufacturerId));
                  $strAssetCode = addslashes(trim($strRowArray[$intAssetCode]));
                  if ($strAssetCode && !$this->in_array_nocase($strAssetCode, $strAssetArray)) {
                    $intLocationKeyArray = array_keys($intLocationArray, addslashes(strtolower(trim($strRowArray[$this->intLocationKey]))));
                    if (!count($intLocationKeyArray)) {
                      $intLocationKeyArray = array_keys($intLocationArray, addslashes(strtolower(trim($this->txtMapDefaultValueArray[$this->intLocationKey]->Text))));
                    }
                    $intModelKeyArray = array_keys($intAssetModelArray, $strAssetModel);
                    if (count($intLocationKeyArray) && count($intModelKeyArray)) {
                      $strAssetArray[] = strtolower($strAssetCode);
                      $objNewAsset = new Asset();
                      $objNewAsset->AssetCode = $strAssetCode;
                      $objNewAsset->LocationId = $intLocationKeyArray[0];
                      $objNewAsset->AssetModelId = $intModelKeyArray[0];
                      /*if (isset($this->intCreatedByKey)) {
                        if (isset($this->intUserArray[strtolower(trim($strRowArray[$this->intCreatedByKey]))])) {
                          $objNewAsset->CreatedBy = $this->intUserArray[strtolower(trim($strRowArray[$this->intCreatedByKey]))];
                        }
                        else {
                          $objNewAsset->CreatedBy = $this->lstMapDefaultValueArray[$this->intCreatedByKey]->SelectedValue;
                        }
                      }*/
                      $objNewAsset->Save();
                      $strCFVArray = array();
                      $objDatabase = CustomField::GetDatabase();
                      // Asset Custom Field import
                      foreach ($arrAssetCustomField as $objCustomField) {
                        if ($objCustomField->CustomFieldQtypeId != 2) {
                          $strShortDescription = (trim($strRowArray[$intAssetCustomFieldKeyArray[$objCustomField->CustomFieldId]])) ?
                                      addslashes(trim($strRowArray[$intAssetCustomFieldKeyArray[$objCustomField->CustomFieldId]])) :
                                      addslashes($this->txtMapDefaultValueArray[$intAssetCustomFieldKeyArray[$objCustomField->CustomFieldId]]->Text);
                          $strQuery = sprintf("INSERT INTO `custom_field_value` " .
                                              "(`custom_field_id`,`short_description`, `created_by`, `creation_date`) " .
                                              "VALUES ('%s', '%s', '%s', 'NOW()');",
                                              $objCustomField->CustomFieldId, $strShortDescription, $_SESSION['intUserAccountId']);
                          $objDatabase->NonQuery($strQuery);
                          $strQuery = sprintf("INSERT INTO `custom_field_selection` " .
                                              "(`entity_id`,`entity_qtype_id`, `custom_field_value_id`) " .
                                              "VALUES ('%s', '%s', '%s');",
                                              $objNewAsset->AssetId, 1, $objDatabase->InsertId(), $_SESSION['intUserAccountId']);
                          $objDatabase->NonQuery($strQuery);
                          $strCFVArray[] = sprintf("`cfv_%s`='%s'", $objCustomField->CustomFieldId, $strShortDescription);
                          /*$objCustomField->CustomFieldSelection = new CustomFieldSelection;
              						$objCustomField->CustomFieldSelection->newCustomFieldValue = new CustomFieldValue;
              						$objCustomField->CustomFieldSelection->newCustomFieldValue->CustomFieldId = $objCustomField->CustomFieldId;
              						if (trim($strRowArray[$intAssetCustomFieldKeyArray[$objCustomField->CustomFieldId]])) {
              						  $objCustomField->CustomFieldSelection->newCustomFieldValue->ShortDescription = addslashes(trim($strRowArray[$intAssetCustomFieldKeyArray[$objCustomField->CustomFieldId]]));
              						}
              						else {
              						  $objCustomField->CustomFieldSelection->newCustomFieldValue->ShortDescription = addslashes($this->txtMapDefaultValueArray[$intAssetCustomFieldKeyArray[$objCustomField->CustomFieldId]]->Text);
              						}
              						$objCustomField->CustomFieldSelection->newCustomFieldValue->Save();
              						$objCustomField->CustomFieldSelection->EntityId = $objNewAsset->AssetId;
              						$objCustomField->CustomFieldSelection->EntityQtypeId = 1;
              						$objCustomField->CustomFieldSelection->CustomFieldValueId = $objCustomField->CustomFieldSelection->newCustomFieldValue->CustomFieldValueId;
              						$objCustomField->CustomFieldSelection->Save();*/
                        }
                        else {
                          $data = trim($strRowArray[$intAssetCustomFieldKeyArray[$objCustomField->CustomFieldId]]);
                          $blnInList = false;
                          $objCustomField->CustomFieldSelection = new CustomFieldSelection;
                    			$objCustomField->CustomFieldSelection->EntityId = $objNewAsset->AssetId;
                    			$objCustomField->CustomFieldSelection->EntityQtypeId = 1;
                    			foreach (CustomFieldValue::LoadArrayByCustomFieldId($objCustomField->CustomFieldId) as $objCustomFieldValue) {
                					  if (strtolower($objCustomFieldValue->ShortDescription) == $data) {
                     					$objCustomField->CustomFieldSelection->CustomFieldValueId = $objCustomFieldValue->CustomFieldValueId;
                    					$blnInList = true;
                    					break;
                					  }
                					}
                					if (!$blnInList && $this->lstMapDefaultValueArray[$intAssetCustomFieldKeyArray[$objCustomField->CustomFieldId]]->SelectedValue != null) {
                 					  $objCustomField->CustomFieldSelection->CustomFieldValueId = $this->lstMapDefaultValueArray[$intAssetCustomFieldKeyArray[$objCustomField->CustomFieldId]]->SelectedValue;
                 					  $objCustomField->CustomFieldSelection->Save();
                					}
                					elseif ($data) {
                					  $objCustomField->CustomFieldSelection->Save();
                					}
                        }
                      }
                      $this->objNewAssetArray[$objNewAsset->AssetId] = $objNewAsset->AssetCode;
                      if (count($strCFVArray)) {
                        $strQuery = sprintf("UPDATE `asset_custom_field_helper` " .
                                            "SET %s " .
                                            "WHERE `asset_id`='%s';", implode(", ", $strCFVArray), $objNewAsset->AssetId);
                        $objDatabase->NonQuery($strQuery);
                      }
                    }
                    // Add records skipped due to errors
                    else {
                      $this->intSkippedRecordCount++;
                      $this->PutSkippedRecordInFile($file_skipped, $strRowArray);
                    }
                  }
                }
              }
              $this->intCurrentFile++;
              break;
            }
            $j++;
          }
          if ($this->intImportStep == 6) {
            $this->blnImportEnd = true;
            $this->btnNext->Warning = "";
            $this->dtgLocation->DataSource = $this->objNewLocationArray;
            $this->dtgCategory->DataSource = $this->objNewCategoryArray;
            $this->dtgManufacturer->DataSource = $this->objNewManufacturerArray;
            $this->dtgAssetModel->DataSource = $this->objNewAssetModelArray;
            $this->dtgAsset->DataSource = $this->objNewAssetArray;
            $this->btnNext->Display = false;
            $this->btnCancel->Display = false;
            $this->btnUndoLastImport->Display = true;
            $this->btnImportMore->Display = true;
            $this->btnReturnToAssets->Display = true;
            $this->lblImportSuccess->Display = true;
            $this->lblImportSuccess->Text = "Success:<br/>" .
                                             "<b>" . count($this->objNewAssetArray) . "</b> Records imported successfully<br/>" .
                                             "<b>" . $this->intSkippedRecordCount . "</b> Records skipped due to error<br/>";
            if ($this->intSkippedRecordCount) {
               $this->lblImportSuccess->Text .= sprintf("<a href='http://%s%s/%s_skipped.csv'>Click here to download records that could not be imported</a><br/>", $_SERVER['SERVER_NAME'] . __SUBDIRECTORY__, __TRACMOR_TMP__, $_SESSION['intUserAccountId']);
            }

            $this->intImportStep = -1;
          }
          // Enable Next button
          $this->btnNext->Enabled = true;
          if (!$this->blnImportEnd && !$this->intCurrentFile) {
            $this->intImportStep++;
          }
        } 
        fclose($file_skipped);
		  }
		  if (!$blnError) {
		    if (($this->blnImportEnd || $this->intImportStep == 2) && $this->intImportStep != -1) {
  		    $this->intStep++;
    		  $this->DisplayStepForm($this->intStep);
		    }
    		if (!$this->blnImportEnd) {
  		    QApplication::ExecuteJavaScript("document.getElementById('".$this->btnNext->ControlId."').click();");
  		  }
        if (!($this->intCurrentFile < count($this->strFilePathArray))) {
          $this->intCurrentFile = 0;
          $this->intImportStep++;
        }
		  }
	  }

	  // Case-insensitive in array function
    protected function in_array_nocase($search, &$array) {
      $search = strtolower($search);
      foreach ($array as $item)
        if (strtolower($item) == $search)
          return TRUE;
      return FALSE;
    }

	  protected function lstMapHeader_Create($objParentObject, $intId, $strName = null) {
	    if ($this->chkHeaderRow->Checked && !$strName) {
	      return false;
	    }
	    $strName = strtolower($strName);
	    $lstMapHeader = new QListBox($objParentObject);
	    $lstMapHeader->Name = "lst".$intId;
	    $strAssetGroup = "Asset";
	    $strAssetModelGroup = "Asset Model";
	    $lstMapHeader->AddItem("- Not Mapped -", null);
	    $lstMapHeader->AddItem("Asset Code", "Asset Code", ($strName == 'asset code') ? true : false, $strAssetGroup);
	    foreach ($this->arrAssetCustomField as $objCustomField) {
	      $lstMapHeader->AddItem($objCustomField->ShortDescription, "asset_".$objCustomField->CustomFieldId,  ($strName == strtolower($objCustomField->ShortDescription)) ? true : false, $strAssetGroup);
	    }
	    $lstMapHeader->AddItem("Location", "Location", ($strName == 'location') ? true : false, $strAssetGroup);
	    $lstMapHeader->AddItem("Created By", "Created By", ($strName == 'created by') ? true : false, $strAssetGroup);
	    $lstMapHeader->AddItem("Created Date", "Created Date", ($strName == 'created date') ? true : false, $strAssetGroup);
	    $lstMapHeader->AddItem("Modified By", "Modified By", ($strName == 'modified by') ? true : false, $strAssetGroup);
	    $lstMapHeader->AddItem("Modified Date", "Modified Date", ($strName == 'modified date') ? true : false, $strAssetGroup);
	    $lstMapHeader->AddItem("Asset Model Code", "Asset Model Code", ($strName == 'asset model code') ? true : false, $strAssetModelGroup);
	    $lstMapHeader->AddItem("Asset Model Short Description", "Asset Model Short Description", ($strName == 'asset model short description') ? true : false, $strAssetModelGroup);
	    $lstMapHeader->AddItem("Asset Model Long Description", "Asset Model Long Description", ($strName == 'asset model long description') ? true : false, $strAssetModelGroup);
	    foreach ($this->arrAssetModelCustomField as $objCustomField) {
	      $lstMapHeader->AddItem($objCustomField->ShortDescription, "model_".$objCustomField->CustomFieldId, ($strName == strtolower($objCustomField->ShortDescription)) ? true : false, $strAssetModelGroup);
	    }
	    $lstMapHeader->AddItem("Category", "Category", ($strName == 'category') ? true : false, $strAssetModelGroup);
	    $lstMapHeader->AddItem("Manufacturer", "Manufacturer", ($strName == 'manufacturer') ? true : false, $strAssetModelGroup);
	    $lstMapHeader->AddAction(new QChangeEvent(), new QAjaxAction('lstTramorField_Change'));
	    $this->lstMapHeaderArray[] = $lstMapHeader;
	    if ($strName && $lstMapHeader->SelectedValue) {
	      $this->arrTracmorField[$intId] = strtolower($lstMapHeader->SelectedValue);
	    }
	    return true;
	  }

	  protected function lstTramorField_Change($strFormId, $strControlId, $strParameter) {
      $objControl = QForm::GetControl($strControlId);
      if ($objControl->SelectedValue != null) {
        $search = strtolower($objControl->SelectedValue);
        if ($this->in_array_nocase($search, $this->arrTracmorField)) {
          $objControl->Warning = "This value has already been selected.";
          $objControl->SelectedIndex = 0;
          unset($this->arrTracmorField[substr($objControl->Name, 3)]);
        }
        else {
          $objControl->Warning = "";
          $txtDefault = $this->txtMapDefaultValueArray[substr($objControl->Name, 3)];
          $lstDefault = $this->lstMapDefaultValueArray[substr($objControl->Name, 3)];
          $dtpDefault = $this->dtpDateArray[substr($objControl->Name, 3)];
          $this->arrTracmorField[substr($objControl->Name, 3)] = $search;
          if (substr($objControl->SelectedValue, 0, 6) == "model_" || substr($objControl->SelectedValue, 0, 6) == "asset_") {
            $objCustomField = CustomField::LoadByCustomFieldId(substr($objControl->SelectedValue, 6));
            // type = Text or TextBox
            if ($objCustomField->CustomFieldQtypeId != 2) {
              $txtDefault->Required = $objCustomField->RequiredFlag;
              // If it is a required text field, then assign the default text for a new entity only
  	 					if ($objCustomField->RequiredFlag && $objCustomField->DefaultCustomFieldValueId) {
  	 						$txtDefault->Text = $objCustomField->DefaultCustomFieldValue->ShortDescription;
  	 					}
  	 					else {
  	 					  $txtDefault->Text = "";
  	 					}
              $txtDefault->Display = true;
              $lstDefault->Display = false;
              $dtpDefault->Display = false;
            }
            // type = Select
            else {
              $lstDefault->RemoveAllItems();
  						$lstDefault->AddItem('- Select One -', null);
  						$lstDefault->Required = true;

  						$objCustomFieldValueArray = CustomFieldValue::LoadArrayByCustomFieldId(substr($objControl->SelectedValue, 6), QQ::Clause(QQ::OrderBy(QQN::CustomFieldValue()->ShortDescription)));
  						if ($objCustomFieldValueArray) {
                foreach ($objCustomFieldValueArray as $objCustomFieldValue) {
  								$objListItem = new QListItem($objCustomFieldValue->__toString(), $objCustomFieldValue->CustomFieldValueId);
  								// If it is a required field, then select the value on new entities by default
  								if ($objCustomField->RequiredFlag && $objCustomField->DefaultCustomFieldValueId && $objCustomField->DefaultCustomFieldValueId == $objCustomFieldValue->CustomFieldValueId) {
  									$objListItem->Selected = true;
  								}
								  $lstDefault->AddItem($objListItem);
  							}
  						}

  						$txtDefault->Display = false;
              $lstDefault->Display = true;
              $dtpDefault->Display = false;
            }
          }
          elseif ($objControl->SelectedValue == "Created By" || $objControl->SelectedValue == "Modified By") {
            $lstDefault->RemoveAllItems();
            foreach ($this->intUserArray as $strUsername => $intUserId) {
              $objListItem = new QListItem($strUsername, $intUserId, ($intUserId != $_SESSION['intUserAccountId']) ? false : true);
              $lstDefault->AddItem($objListItem);
            }
            $txtDefault->Display = false;
            $lstDefault->Display = true;
            $dtpDefault->Display = false;
          }
          elseif ($objControl->SelectedValue == "Created Date" || $objControl->SelectedValue == "Modified Date") {
            $txtDefault->Display = false;
            $lstDefault->Display = false;
            $dtpDefault->Display = true;
          }
          else {
            $txtDefault->Display = true;
            $lstDefault->Display = false;
            $dtpDefault->Display = false;
          }
        }
      }
      else {
        unset($this->arrTracmorField[substr($objControl->Name, 3)]);
      }
	  }

	  protected function GetCsvSettings() {
	    switch ($this->lstFieldSeparator->SelectedValue) {
	      case 1:
          $strSeparator = ",";
          break;
	      case 2:
	        $strSeparator = "\t";
	        break;
	      default:
	        $strSeparator = $this->txtFieldSeparator->Text;
	        break;
	    }
	    switch ($this->lstTextDelimiter->SelectedValue) {
	      case 1:
          $strDelimiter = null;
          break;
	      case 2:
	        $strDelimiter = "'";
	        break;
	      case 3:
	        $strDelimiter = '"';
	        break;
	      default:
	        $strDelimiter = $this->txtTextDelimiter->Text;
	        break;
	    }
	    return $settings = array(
              'delimiter' => $strSeparator,
              'eol' => ";",
              'length' => null,
              'escape' => $strDelimiter
             );
	  }

    protected function DisplayStepForm($intStep) {
      switch ($intStep) {
       case 1:
         $this->pnlMain->RemoveChildControls($this->pnlMain);
		     $this->pnlStepOne_Create();
		     break;
		   case 2:
		     $this->pnlMain->RemoveChildControls($this->pnlMain);
		     $this->pnlStepTwo_Create();
		     break;
		   case 3:
		     $this->pnlMain->RemoveChildControls($this->pnlMain);
		     $this->pnlStepThree_Create();
		     break;
		   case 4:
		     $this->DisplayStepForm(1);
		     $this->intStep = 1;
		     break;
      }
    }

		// Cancel button click action
		protected function btnCancel_Click() {
		  $this->UndoImport();
      QApplication::Redirect("./asset_import.php");
    }

    protected function btnImportMore_Click() {
      QApplication::Redirect("./asset_import.php");
    }

    protected function btnReturnToAssets_Click() {
      QApplication::Redirect("../assets/asset_list.php");
    }

    // Delete All imported Assets, Asset Models, Manufacturers, Categories and Locations
    protected function UndoImport() {
      $objDatabase = Asset::GetDatabase();
		  if (count($this->objNewAssetArray)) {
        $strQuery = sprintf("DELETE FROM `asset` WHERE `asset_id` IN (%s)", implode(", ", array_keys($this->objNewAssetArray)));
        $objDatabase->NonQuery($strQuery);
		  }
		  if (count($this->objNewAssetModelArray)) {
        $strQuery = sprintf("DELETE FROM `asset_model` WHERE `asset_model_id` IN (%s)", implode(", ", array_keys($this->objNewAssetModelArray)));
        $objDatabase->NonQuery($strQuery);
		  }
		  if (count($this->objNewManufacturerArray)) {
        $strQuery = sprintf("DELETE FROM `manufacturer` WHERE `manufacturer_id` IN (%s)", implode(", ", array_keys($this->objNewManufacturerArray)));
        $objDatabase->NonQuery($strQuery);
		  }
		  if (count($this->objNewCategoryArray)) {
        $strQuery = sprintf("DELETE FROM `category` WHERE `category_id` IN (%s)", implode(", ", array_keys($this->objNewCategoryArray)));
        $objDatabase->NonQuery($strQuery);
		  }
		  if (count($this->objNewLocationArray)) {
        $strQuery = sprintf("DELETE FROM `location` WHERE `location_id` IN (%s)", implode(", ", array_keys($this->objNewLocationArray)));
        $objDatabase->NonQuery($strQuery);
		  }
    }

    protected function PutSkippedRecordInFile ($file, $strRowArray) {
      fputcsv($file, $strRowArray, $this->FileCsvData->settings['delimiter'], $this->FileCsvData->settings['eol']);
    }

    protected function btnAddField_Click() {
 	      $intTotalCount = count($this->lstMapHeaderArray);
 	      $this->lstMapHeader_Create($this, $intTotalCount-1, ($this->chkHeaderRow->Checked) ? "addfield" : null);
 	      $objTemp = $this->lstMapHeaderArray[$intTotalCount];
 	      $this->lstMapHeaderArray[$intTotalCount] = $this->lstMapHeaderArray[$intTotalCount-1];
 	      $this->lstMapHeaderArray[$intTotalCount-1] = $objTemp;
 	      $txtDefaultValue = new QTextBox($this);
 	      $txtDefaultValue->Width = 200;
 	      $this->txtMapDefaultValueArray[] = $txtDefaultValue;
 	      $lstDefaultValue = new QListBox($this);
 	      $lstDefaultValue->Width = 200;
 	      $lstDefaultValue->Display = false;
 	      $this->lstMapDefaultValueArray[] = $lstDefaultValue;
 	      $dtpDate = new QDateTimePicker($this);
 	      $dtpDate->DateTimePickerType = QDateTimePickerType::Date;
 	      $dtpDate->DateTimePickerFormat = QDateTimePickerFormat::MonthDayYear;
 	      $dtpDate->Display = false;
 	      $this->dtpDateArray[] = $dtpDate;

 	      $btnRemove = new QButton($this);
 	      $btnRemove->Text = "Remove";
 	      $btnRemove->ActionParameter = $intTotalCount-1;
 	      $btnRemove->AddAction(new QClickEvent(), new QServerAction('btnRemove_Click'));
 	      $btnRemove->AddAction(new QEnterKeyEvent(), new QServerAction('btnRemove_Click'));
 	      $btnRemove->AddAction(new QEnterKeyEvent(), new QTerminateAction());

 	      if (isset($this->arrMapFields[$intTotalCount-1])) {
 	        unset($this->arrMapFields[$intTotalCount-1]);
 	      }

 	      $this->btnRemoveArray[$intTotalCount-1] = $btnRemove;
 	    }

 	    protected function btnRemove_Click($strFormId, $strControlId, $strParameter) {
 	      $intId = (int)$strParameter;
 	      $intTotalCount = count($this->lstMapHeaderArray)-1;

 	      if ($intId < count($this->lstMapHeaderArray)-2) {
 	        for ($i=$intId; $i<count($this->lstMapHeaderArray)-1; $i++) {
 	          $this->lstMapHeaderArray[$i] = $this->lstMapHeaderArray[$i+1];
 	          if (isset($this->txtMapDefaultValueArray[$i+1])) {
 	            $this->txtMapDefaultValueArray[$i] = $this->txtMapDefaultValueArray[$i+1];
 	            $this->lstMapDefaultValueArray[$i] = $this->lstMapDefaultValueArray[$i+1];
 	            $this->dtpDateArray[$i] = $this->dtpDateArray[$i+1];
 	            $this->btnRemoveArray[$i] = $this->btnRemoveArray[$i+1];
 	            $this->btnRemoveArray[$i]->ActionParameter = $i;
 	          }
 	          else {
 	            unset($this->txtMapDefaultValueArray[$i]);
 	            unset($this->lstMapDefaultValueArray[$i]);
 	            unset($this->dtpDateArray[$i]);
 	            unset($this->btnRemoveArray[$i]);
 	          }
 	        }
 	        unset($this->lstMapHeaderArray[$intTotalCount]);
 	      }
 	      else {
 	        $this->lstMapHeaderArray[$intId] = $this->lstMapHeaderArray[$intId+1];
 	        unset($this->lstMapHeaderArray[$intId+1]);
 	        unset($this->txtMapDefaultValueArray[$intId]);
 	        unset($this->lstMapDefaultValueArray[$intId]);
 	        unset($this->dtpDateArray[$intId]);
 	        unset($this->btnRemoveArray[$intId]);
 	      }
 	    }

	}
	// Go ahead and run this form object to generate the page
	AdminLabelsForm::Run('AdminLabelsForm', 'asset_import.tpl.php');
?>