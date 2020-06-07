<?php

// :: 13/02/2020 :: bit of a messy class, may need a clean at some stage (to improve maintainability)
$arr_file_types = ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
require '../vendor/autoload.php';

function convert_excel_to_sqlite()
{
    /**
     * Variables
     */
    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
    $reader->setReadDataOnly(TRUE);
    $spreadsheet = $reader->load("../uploads/EAMs.xlsx");
    $worksheet = $spreadsheet->getActiveSheet();

    $database = new PDO('sqlite:../db/EAMs.db');
    $database->exec("DROP TABLE IF EXISTS EAMS"); // dropping EAMS table (if it exists)

    $create_table_query = "CREATE TABLE IF NOT EXISTS EAMS (rowID INTEGER PRIMARY KEY,"; // the create table script
    $insert_table_query = "INSERT INTO EAMS ("; // the insert script

    $highest_column = $worksheet->getHighestColumn();

    /**
     * First Row
     */
    $values = "VALUES (";
    foreach ($worksheet->getRowIterator(1)->current()->getCellIterator() as $cell)
    {
        $create_table_query .= "'" . $cell->getValue() . "'" . ($cell->getColumn() != $highest_column ? " TEXT, " : " TEXT)");
        $insert_table_query .= "'" . $cell->getValue() . "'" . ($cell->getColumn() != $highest_column ? ", " : ") ");
        $values .= ($cell->getColumn() != $highest_column ? "?, " : "?)");
    }

    /**
     * Other Rows
     */
    $database->exec($create_table_query); // creating table
    $insert_table_query .= $values;

    $stmt = $database->prepare($insert_table_query);
    foreach($worksheet->getRowIterator(2) as $row)
    {
        $pRange = 'A'.$row->getRowIndex().':'.$highest_column.$row->getRowIndex();
        $rowAsArray = $worksheet->rangeToArray($pRange, null, false, false, false);
        $stmt->execute($rowAsArray[0]);
    }

    /**
     * Inserting data...?
     */
    $database->exec("DROP TABLE IF EXISTS Excluded_Columns");
    $database->exec("CREATE TABLE IF NOT EXISTS Excluded_Columns (rowID INTEGER PRIMARY KEY, Name TEXT)");
    $database->exec("INSERT INTO Excluded_Columns (Name) VALUES ('rowID')");
}

if (!(in_array($_FILES['file']['type'], $arr_file_types)))
{
    echo "Error uploading file. Ensure file is an Excel document.";
    return;
}

if (!file_exists('uploads'))
{
    mkdir('uploads', 0777);
}

move_uploaded_file($_FILES['file']['tmp_name'], '../uploads/' . $_FILES['file']['name']);
echo "File uploaded successfully. You may safely leave the page.";

convert_excel_to_sqlite();