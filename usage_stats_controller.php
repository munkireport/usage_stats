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
     * Get Thermal Pressure counts for button widget
     *
     * Returns JSON in the form:
     * [{ "label": "Nominal", "count": 123 }, ...]
     *
     * @return void
     **/
    public function get_thermal_pressure_stats()
    {
        $sql = "SELECT
                    COUNT(*) AS count,
                    CASE
                        WHEN LOWER(thermal_pressure) = 'nominal' THEN 'Nominal'
                        WHEN LOWER(thermal_pressure) = 'moderate' THEN 'Moderate'
                        WHEN LOWER(thermal_pressure) = 'heavy' THEN 'Heavy'
                        WHEN LOWER(thermal_pressure) = 'critical' THEN 'Critical'
                        ELSE 'Unknown'
                    END AS label
                FROM usage_stats
                LEFT JOIN reportdata USING (serial_number)
                ".get_machine_group_filter()."
                AND thermal_pressure <> '' AND thermal_pressure IS NOT NULL
                GROUP BY label
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
