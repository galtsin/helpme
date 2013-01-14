<?php
/**
 * Product: HELPME
 * @author: GaltsinAK
 */
/**
 *
 */
class App_Core_Report
{
    /**
     * @var App_Core_Report[]
     */
    private $_reports = array();

    public function generate(){
        $html = '';
        foreach($this->_reports as $report) {
            $html += $report->generate();
        }
        return $html;
    }

    public function add(App_Core_Report $report){
        $this->_reports[] = $report;
    }
}
