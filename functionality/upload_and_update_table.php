<?php

    // :: 13/02/2020 :: bit of a messy class, may need a clean at some stage (to improve maintainability)
	$arr_file_types = ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
	require '../vendor/autoload.php';

    function convert_excel_to_sqlite()
    {
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(TRUE);
        $spreadsheet = $reader->load("../uploads/EAMs.xlsx");
        $worksheet = $spreadsheet->getActiveSheet();

        $database = new PDO('sqlite:../db/EAMs.db');
        $database->exec("DROP TABLE IF EXISTS EAMS"); // dropping EAMS table (if it exists)

        $first_row = true;
        $row_count = 0;		// used to count amount of rows
        $column_count = 0;	// used to count number of columns
        $row_iteration = 0;	// used to keep track of iteration in loop below
        $create_table_query = "CREATE TABLE IF NOT EXISTS EAMS (rowID INTEGER PRIMARY KEY,"; // the create table script
        $insert_table_query = "INSERT INTO EAMS ("; // the insert script

        foreach ($worksheet->getRowIterator() as $row)
        {
            $row_count++; // setting the amount of rows (corresponding to excel file)
        }

        foreach ($worksheet->getRowIterator() as $row)
        {
            $unparsed_cells = $row->getCellIterator();
            $unparsed_cells->setIterateOnlyExistingCells(FALSE);
            $cell_iteration = 0;
            $row_iteration++;	// incrementing current row in for loop

            if ($first_row)
            { // if this is our first row...
                foreach ($unparsed_cells as $cell)
                {
                    $column_count++; // setting the amount of columns (corresponding to excel file)
                }

                foreach ($unparsed_cells as $cell)
                { // iterating through each cell in focused row
                    $cell_iteration++; // incrementing focused cell
                    $create_table_query .= "'" . $cell->getValue() . "'" . ($cell_iteration !== $column_count ? " TEXT, " : " TEXT)");
                    $insert_table_query .= "'" . $cell->getValue() . "'" . ($cell_iteration !== $column_count ? ", " : ") ");
                }

                $insert_table_query .= "VALUES "; // concates this to our insert table script after loop finishes
                $first_row = false; // no longer the first row
            }
            else
            {
                $insert_table_query .= "(";

                foreach ($unparsed_cells as $cell)
                {
                    $cell_iteration++;
                    $insert_table_query .= "'" . $cell->getValue() . "'" . ($cell_iteration !== $column_count ? ", " : ")");
                }

                $insert_table_query .= ($row_iteration !== $row_count ? ", " : "");
            }
        }

        $database->exec($create_table_query);
        $database->exec($insert_table_query);
        $database->exec("DROP TABLE Excluded_Columns");
        $database->exec("CREATE TABLE IF NOT EXISTS Excluded_Columns (rowID INTEGER PRIMARY KEY, Name TEXT)");
        $database->exec("INSERT INTO Excluded_Columns (Name) VALUES ('rowID')");
    }

	if (!(in_array($_FILES['file']['type'], $arr_file_types)))
	{
	  echo "false";
	  return;
	}

	if (!file_exists('uploads'))
	{
	  mkdir('uploads', 0777);
	}

	move_uploaded_file($_FILES['file']['tmp_name'], '../uploads/' . $_FILES['file']['name']);
	echo "File uploaded successfully. You may safely leave the page.";

    convert_excel_to_sqlite();