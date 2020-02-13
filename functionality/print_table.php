<?php

	require_once ("model.php");
	
	class print_table extends model {
		
		private $database, $total_records, $limit, $offset, $current_page, $page_quantity;

		public function __construct($pdo)
		{
			parent::__construct($pdo);
			$this->database = $pdo;
			$this->limit = 200;
		}

		public function get_selection_query() 
		{
			$excluded_columns = parent::get_excluded_columns();
			$stmt = "";
			
			foreach (parent::get_columns() as $column)
			{
				$stmt .= (in_array($column, $excluded_columns) ? "" : ($stmt == "" ? "SELECT " : " ")."`".$column."`,");
			}

			return ($stmt === "" ? "" : substr($stmt, 0, -1)." FROM EAMS");
		}

		public function get_where_clause() 
		{
			$excluded_columns = parent::get_excluded_columns();
			$stmt = "";
			
			foreach (parent::get_columns() as $column)
			{
				$stmt .= (in_array($column, $excluded_columns) ? "" : ($stmt == "" ? " WHERE" : " OR")." `".$column."` LIKE '%".$_GET['search']."%'");
			}
			
			return $stmt;
		}

		public function attempt_print_table_and_navigator($where_clause, $select_query)
		{
            if ($select_query === "") // if all columns are disabled, print error...
            {
                $this->print_error();
            }
			else // ...else print table and navigator
            {
                $this->print_table_and_navigator($where_clause, $select_query);
            }
		}

        public function print_error()
        {
            echo "<div class=\"align-center\"><table class=\"table\"><thead><td><h2>All columns disabled in Admin panel, re-enable some using the toggle box provided!</h2></td></thead></table></div>";
        }

        public function print_table_and_navigator($where_clause, $select_query)
        {
            $this->total_records = parent::count_records("EAMS".$where_clause);
            $this->page_quantity = ceil($this->total_records / $this->limit);
            $this->current_page = min($this->page_quantity, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, array('options' => array('default'   => 1,'min_range' => 1,),)));
            $this->offset = ($this->current_page - 1) * $this->limit;

            $stmt = $this->database->prepare($select_query.$where_clause." LIMIT ".$this->limit." OFFSET ".$this->offset);
            $stmt->execute();

            if ($this->total_records < 1)
            {
                echo '<div class="align-center"><p>No results could be displayed.</p></div>';
                return;
            }

            $this->print_table_navigator();
            echo '<div class="align-center"><table class="table">';
            $firstRow = true;

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
            {
                if ($firstRow === true)
                {
                    echo '<thead><tr>';

                    foreach ($row as $key => $value)
                    {
                        echo '<th>'.$key.'</th>';
                    }

                    echo '</tr></thead><tbody>';
                    $firstRow = false;
                }

                echo '<tr>';

                foreach ($row as $value)
                {
                    echo '<td>'.$value.'</td>';
                }

                echo '</tr>';
            }
            echo '</tbody></table></div>';

            if ($this->total_records > 20)
            {
                $this->print_table_navigator();
            }
        }

		public function print_table_navigator()
		{
			$top = $this->offset + 1;
			$bottom = min($this->offset + $this->limit, $this->total_records);
			
			$search_data = (isset($_GET['action']) ? '?search='.$_GET['search'].'&action=Search&' : '?');	
			$previous = '<a href="'.$search_data.'page=1" title="First">&laquo;</a>
						 <a href="'.$search_data.'page='.($this->current_page - ($this->current_page > 1 ? 1 : 0)).'" title="Previous">&lsaquo;</a>';
		
			$next = '<a href="'.$search_data.'page='.($this->current_page + ($this->current_page < $this->page_quantity ? 1 : 0)).'" title="Next">&rsaquo;</a>
					 <a href="'.$search_data.'page='.$this->page_quantity.'" title="End">&raquo;</a>';
			
			echo '<div class="align-center">',
					'<div class="table-navigation">',
						'<form class="search-bar" method="GET">',	
							'<input class ="search-box" type="text" name="search" placeholder="Enter Search Here">',
							'<input class ="search-button" type="submit" value="Search" name="action">',
						'</form>',		
						'<div class ="pagination">',
							'<p> Page ', $this->current_page, ' of ', $this->page_quantity, ' pages, displaying ', $top, '-', $bottom, ' of ', $this->total_records, ' results </p>', $previous, $next, 
						'</div>',
					'</div>',
				'</div>';
		}
		
	}