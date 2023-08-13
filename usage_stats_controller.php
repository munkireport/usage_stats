<?php

/**
 * usage_stats module class
 *
 * @package munkireport
 * @author tuxudo
 **/
class Usage_stats_controller extends Module_controller
{

	/*** Protect methods with auth! ****/
	function __construct()
	{
		// Store module path
		$this->module_path = dirname(__FILE__);
	}

	/**
	* Default method
	* @author AvB
	*
	**/
	function index()
	{
		echo "You've loaded the usage_stats module!";
	}

     /**
     * Get data for scroll widget
     *
     * @return void
     * @author tuxudo
     **/
    public function get_scroll_widget($column)
    {
        // Remove non-column name characters
        $column = preg_replace("/[^A-Za-z0-9_\-]]/", '', $column);

        $sql = "SELECT COUNT(CASE WHEN ".$column." <> '' AND ".$column." IS NOT NULL THEN 1 END) AS count, ".$column." 
                FROM usage_stats
                LEFT JOIN reportdata USING (serial_number)
                ".get_machine_group_filter()."
                AND ".$column." <> '' AND ".$column." IS NOT NULL 
                GROUP BY ".$column."
                ORDER BY count DESC";

        $queryobj = new Usage_stats_model;
        jsonView($queryobj->query($sql));
    }

	/**
     * Retrieve data in json format
     *
     **/
    public function get_data($serial_number = '')
    {
        $obj = new View();
        $usage = new Usage_stats_model($serial_number);
        $obj->view('json', array('msg' => $usage->rs));
    }

} // END class Usage_stats_controller
