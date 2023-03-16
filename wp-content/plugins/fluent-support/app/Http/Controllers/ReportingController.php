<?php

namespace FluentSupport\App\Http\Controllers;

use FluentSupport\App\Modules\Reporting\Reporting;
use FluentSupport\App\Modules\StatModule;
use FluentSupport\App\Services\Helper;
use FluentSupport\Framework\Request\Request;

/**
 * ReportingController class for REST API
 * This class is responsible for getting data for all request related to report
 * @package FluentSupport\App\Http\Controllers
 *
 * @version 1.0.0
 */
class ReportingController extends Controller
{
    /**
     * getOverallReports method will return the overall statistics of all ticket by ticket statuses
     * The response will have an array with ticket number by ticket status
     * @param Request $request
     * @return array
     */
    public function getOverallReports(Request $request)
    {
        return [
            'overall_reports' => StatModule::getOverAllStats(),
            'today_reports' => StatModule::getTodayStats(),
        ];
    }

    public function getActiveTicketsByProduct()
    {
        return [
            'stats' => StatModule::getActiveTicketsByProductStats()
        ];
    }

    /**
     * getTicketsChart method will generate statistics for all tickets within a date range and return ticket number by date
     * @param Request $request
     * @param Reporting $reporting
     * @return array
     */
    public function getTicketsChart(Request $request, Reporting $reporting)
    {
        list($from, $to) = $request->getSafe('date_range') ?: ['', ''];
        $filter = [];
        $stats = $reporting->getTicketsGrowth($from, $to);

        if($agent_id = $request->getSafe('agent_id', '', 'intval')) {
            $filter['agent_id'] = $agent_id;
            $stats = $reporting->getTicketsGrowth($from, $to, $filter);
        }
        return [
            'stats' => $stats
        ];
    }

    /**
     * getResolveChart method will generate statistics for closed tickets within a date range and return ticket number by date
     * @param Request $request
     * @param Reporting $reporting
     * @return array
     */
    public function getResolveChart(Request $request, Reporting $reporting)
    {
        list($from, $to) = $request->getSafe('date_range') ?: ['', ''];
        $filter = [];
        $stats = $reporting->getTicketResolveGrowth($from, $to);

        if($agent_id = $request->getSafe('agent_id', '', 'intval')) {
            $filter['agent_id'] = $agent_id;
            $stats = $reporting->getTicketResolveGrowth($from, $to, $filter);
        }

        return [
            'stats' => $stats
        ];
    }

    /**
     * getResponseChart method will generate response statistics for ticket by date range
     * @param Request $request
     * @param Reporting $reporting
     * @return array
     */
    public function getResponseChart(Request $request, Reporting $reporting)
    {
        list($from, $to) = $request->getSafe('date_range') ?: ['', ''];
        $filter = [];
        $stats = $reporting->getResponseGrowth($from, $to);

        if($person_id = $request->getSafe('agent_id', '', 'intval')) {
            $filter['person_id'] = $person_id;
            $stats = $reporting->getResponseGrowth($from, $to, $filter);
        }

        return [
            'stats' => $stats
        ];
    }

    /**
     * getAgentsSummary method will generate summary for agent
     * This method will count closed tickets, open tickets, responses/interactions with ticket by agent within a date range
     * @param Request $request
     * @param Reporting $reporting
     * @return array
     */
    public function getAgentsSummary(Request $request, Reporting $reporting)
    {
        return [
          'summary' =>  $reporting->agentSummary($request->getSafe('from'), $request->getSafe('to'))
        ];
    }

    /**
     * getAgentOverallReports method will return the overall statistics report for logged-in agent
     * @param Request $request
     * @return array
     */
    public function getAgentOverallReports(Request $request)
    {
        $agent =  Helper::getAgentByUserId(get_current_user_id());

        return [
            'overall_reports' => StatModule::getAgentOverallStats($agent->id),
            'today_reports' => StatModule::getTodayStats($agent->id)
        ];
    }

    /**
     * getAgentResolveChart method will generate ticket data for resolved ticket
     * @param Request $request
     * @param Reporting $reporting
     * @return array
     */
    public function getAgentResolveChart(Request $request, Reporting $reporting)
    {
        //Get logged in agent information
        $agent =  Helper::getAgentByUserId(get_current_user_id());
        list($from, $to) = $request->getSafe('date_range') ?: ['', ''];

        return [
            'stats' => $reporting->getTicketResolveGrowth($from, $to, ['agent_id' => $agent->id])
        ];
    }

    /**
     * getAgentResponseChart method will generate the statistics of response by agent in tickets within date range
     * @param Request $request
     * @param Reporting $reporting
     * @return array
     */
    public function getAgentResponseChart(Request $request, Reporting $reporting)
    {
        $agent =  Helper::getAgentByUserId(get_current_user_id());
        list($from, $to) = $request->getSafe('date_range') ?: ['', ''];

        return [
            'stats' => $reporting->getResponseGrowth($from, $to, ['person_id' => $agent->id])
        ];
    }

    /**
     * getPersonalSummary method will generate summary for specific agent
     * This method will count closed tickets, open tickets, responses/interactions with ticket by agent within a date range
     * @param Reporting $reporting
     * @param Request $request
     * @return array
     */
    public function getPersonalSummary(Reporting $reporting, Request $request)
    {
        $agent =  Helper::getAgentByUserId(get_current_user_id());

        return [
            'summary' =>  $reporting->agentSummary($request->getSafe('from'), $request->getSafe('to'), $agent->id)
        ];
    }
}
