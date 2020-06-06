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
    $highest_row = $worksheet->getHighestRow();

    /**
     * First Row
     */
    $first_row = $worksheet->getRowIterator(1)->current();
    $unparsed_cells = $first_row->getCellIterator();
    $unparsed_cells->setIterateOnlyExistingCells(FALSE);

    $values = "VALUES (";
    foreach ($unparsed_cells as $cell)
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

    $i = 1;
    foreach($worksheet->getRowIterator(2) as $row)
    {
        $stmt = $database->prepare($insert_table_query);
        $unparsed_cells = $row->getCellIterator();
        $unparsed_cells->setIterateOnlyExistingCells(FALSE);
        foreach ($unparsed_cells as $cell)
        {
            $stmt->bindValue($i, $cell->getValue());
            $i++;
        }
        $stmt->execute();
        $i = 1;
    }

    /**
     * Inserting data...?
     */
    //$database->exec($insert_table_query);
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