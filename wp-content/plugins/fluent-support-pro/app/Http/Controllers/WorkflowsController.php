<?php

namespace FluentSupportPro\App\Http\Controllers;

use FluentSupport\App\Http\Controllers\Controller;
use FluentSupport\App\Models\Ticket;
use FluentSupport\App\Modules\PermissionManager;
use FluentSupport\App\Services\Helper;
use FluentSupport\Framework\Request\Request;
use FluentSupport\Framework\Support\Arr;
use FluentSupportPro\App\Models\Workflow;
use FluentSupportPro\App\Models\WorkflowAction;
use FluentSupportPro\App\Services\Workflow\WorkflowHelper;
use FluentSupportPro\App\Services\Workflow\WorkflowRunner;

class WorkflowsController extends Controller
{

    /**This method get the list of workflows from the fs_workflows table and return
     * @param Request $request
     * @return array
     */
    public function index(Request $request)
    {
        $workflows = Workflow::orderBy('id', 'DESC')
            ->paginate();

        $triggers = WorkflowHelper::getTriggers();

        foreach ($workflows as $workflow) {
            if ($workflow->trigger_type == 'automatic' && $workflow->trigger_key) {
                $workflow->trigger_human_name = Arr::get($triggers, 'triggers.' . $workflow->trigger_key . '.title');
            }
        }

        return [
            'workflows' => $workflows
        ];
    }

    public function create(Request $request)
    {
        $data = $request->all();
        $this->validate($data, [
            'title' => 'required|unique:fs_workflows'
        ]);

        $workflow = Workflow::create($data);

        return [
            'workflow' => $workflow,
            'message'  => 'Workflow has been created'
        ];
    }

    public function getWorkflow(Request $request, $workflowId)
    {
        //get the list of workflows from fs_workflows by workflow id
        $workflow = Workflow::findOrFail($workflowId);

        $data = [
            //Get list of workflow  actions from fs_workflow_actions by workflow id
            'actions' => WorkflowAction::where('workflow_id', $workflow->id)->get()
        ];

        //Get list of action field if request comes with action_fields
        if (in_array('action_fields', $request->get('with', []))) {
            $data['action_fields'] = WorkflowHelper::getActions($workflow);
        }

        if (in_array('trigger_fields', $request->get('with', []))) {
            $data['trigger_fields'] = WorkflowHelper::getTriggers($workflow);
        }

        $workflow = $workflow->toArray();

        if ($workflow['trigger_type'] == 'automatic' && empty($workflow['settings']['conditions'])) {
            if (!is_array($workflow['settings'])) {
                $workflow['settings'] = [];
            }
            $workflow['settings']['conditions'] = [[]];
        }

        $data['workflow'] = $workflow;

        return $data;

    }

    public function updateWorkflow(Request $request, $workflowId)
    {
        $workflow = Workflow::findOrFail($workflowId);
        $workFlowData = $request->get('workflow', []);
        $this->validate($workFlowData, [
            'title' => 'required'
        ]);

        $title = sanitize_text_field($workFlowData['title']);

        if (Workflow::where('title', $title)->where('id', '!=', $workflowId)->first()) {
            return $this->sendError([
                'message' => 'Workflow title needs to be unique'
            ]);
        }

        $workFlowData['title'] = $title;
        $workflow->fill($workFlowData)->save();


        $actions = $request->get('actions', []);
        WorkflowHelper::syncActions($workflowId, $actions);

        return [
            'message'  => 'Workflow has been updated',
            'workflow' => $workflow,
            'actions'  => WorkflowAction::where('workflow_id', $workflow->id)->get()
        ];
    }

    public function getWorkflowActions(Request $request, $workflowId)
    {
        $workflow = Workflow::findOrFail($workflowId);

        $actions = WorkflowAction::select(['id', 'title', 'action_name'])->where('workflow_id', $workflow->id)->get();

        return [
            'actions' => $actions
        ];
    }

    public function runWorkFlow(Request $request, $workflowId)
    {
        $ticketIds = array_filter(array_filter($request->get('ticket_ids', []), 'absint'));

        if (!$ticketIds) {
            return $this->sendError([
                'message' => 'No ticket found'
            ]);
        }

        $workflow = Workflow::findOrFail($workflowId);

        if ($workflow->status != 'published' || $workflow->trigger_type != 'manual') {
            return $this->sendError([
                'message' => 'The selected workflow needs to be published and trigger type manual'
            ]);
        }

        $actions = WorkflowAction::where('workflow_id', $workflow->id)->get();

        if ($actions->isEmpty()) {
            return $this->sendError([
                'message' => 'No Actions found for this workflow'
            ]);
        }

        $ticketsQuery = Ticket::whereIn('id', $ticketIds);

        do_action_ref_array('fluent_support/tickets_query_by_permission_ref', [&$ticketsQuery, false]);

        $tickets = $ticketsQuery->get();

        if ($tickets->isEmpty()) {
            return $this->sendError([
                'message' => 'No tickets found based on your permission for this workflow'
            ]);
        }

        $didRun = false;
        foreach ($tickets as $ticket) {
            $result = (new WorkflowRunner($workflow, $ticket, $actions))->runActions();
            if ($result) {
                $didRun = true;
            }
        }

        if (!$didRun) {
            return $this->sendError([
                'message' => 'Actions could not be executed'
            ]);
        }

        return [
            'message' => 'Selected workflow actions has been successfully applied'
        ];

    }

    public function deleteWorkflow(Request $request, $workflowId)
    {
        Workflow::where('id', $workflowId)->delete();
        WorkflowAction::where('workflow_id', $workflowId)->delete();

        return [
            'message' => __('Selected workflow has been deleted', 'fluent-support-pro')
        ];
    }
}
