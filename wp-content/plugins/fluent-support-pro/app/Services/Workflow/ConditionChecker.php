<?php

namespace FluentSupportPro\App\Services\Workflow;

use FluentSupport\App\Services\Helper;
use FluentSupportPro\App\Services\TicketBookmarkService;

class ConditionChecker
{
	public function check($workFlow, $source, $customer)
	{
		$conditionGroups = $workFlow->settings['conditions'];

		$isTriggeredByTicket = strpos($workFlow->trigger_key, 'ticket');

		foreach ($conditionGroups as $conditionGroup) {
			if (!$this->matchTicketConditionGroup($conditionGroup, $source, $customer, $isTriggeredByTicket)) {
				return false;
			}
		}

		return true;
	}

	private function matchTicketConditionGroup($conditionGroup, $source, $customer, $isTriggeredByTicket)
	{
		foreach ($conditionGroup as $condition) {
			$dataKey = $condition['data_key'];
			$operator = $condition['data_operator'];
			$accessor = explode('.', $dataKey);

			if (count($accessor) != 2 || !$operator) {
				return true;
			}

			$dataProvider = $accessor[0];
			$condition['property'] = $accessor[1];

			switch ($dataProvider) {
				case 'customer':
					$target = $customer;
					break;
				case 'ticket':
					$target = $isTriggeredByTicket ? $source : $source->ticket;
					break;
				default:
					$target = $source;
			}

			$match = $this->match($condition, $target);

			if ($match) {
				return true;
			}
		}

		return false;
	}

	private function match($condition, $target)
	{
		switch ($condition['data_operator']) {
			case 'contains':
				return mb_stripos($target->{$condition['property']}, $condition['data_value']) !== false;
			case 'not_contains':
				return mb_stripos($target->{$condition['property']}, $condition['data_value']) == false;
			case 'yes':
				return $target->attachments->count();
			case 'no':
				return !$target->attachments->count();
			case 'range':
				if ($condition['property'] === 'added_time_range') {
					$target = strtotime(date('H:i:s', strtotime($target->created_at)));
				} else {
					$target = strtotime($target->created_at);
				}
				return $target >= strtotime($condition['value_1']) && $target <= strtotime($condition['value_2']);
			case '>':
				return strtotime($target->created_at) > strtotime($condition['data_value']);
			case '<':
				return strtotime($target->created_at) < strtotime($condition['data_value']);
			case 'equal':
				return $target->{$condition['property']} == $condition['data_value'];
			case 'not_equal':
				return $target->{$condition['property']} != $condition['data_value'];
            case 'includes_in':
                $customer = $target->customer->id;
                if($target->conversation_type == 'response'){
                    $customer = $target->person_id;
                }

                if( $condition['property'] == 'tag_id'){
                    return $this->matchBookmarks($condition, $target);
                }

                return $this->matchCRMTagsOrLists($condition['property'], $condition['data_value'], $customer);
            case 'not_includes_in':
                $customer = $target->customer->id;
                if($target->conversation_type == 'response'){
                    $customer = $target->person_id;
                }

                if( $condition['property'] == 'tag_id'){
                    return $this->matchBookmarks($condition, $target);
                }

                return !$this->matchCRMTagsOrLists($condition['property'], $condition['data_value'], $customer);
		}

		return false;
	}

    private function matchBookmarks($condition, $target){
        $ticketId = $target->ticket->id;
        $values = $condition['data_value'];//condition values from workflow
        $bookmarks = (new TicketBookmarkService())->getExistingBookmarks($ticketId)->toArray();//Bookmarks exists for ticket by id

        $match = false;
        switch ($condition['data_operator']) {
            case 'includes_in':
                $match = count(array_intersect($values, array_column($bookmarks, 'tag_id'))) == count($values);
                break;
            case 'not_includes_in':
                $match = count(array_intersect($values, array_column($bookmarks, 'tag_id'))) != count($values);
                break;
        }

        return $match;
    }

    private function matchCRMTagsOrLists($type, $condition, $customer)
    {
        $customer = Helper::getCustomerByID($customer);

        if(!$customer){
            return false;
        }

        if(function_exists('\FluentCrmApi')) {
            $crmUser= \FluentCrmApi('contacts')->getContact($customer->email);
        }

        if(!$crmUser){
            return false;
        }

        if($type=='lists') {
            if($crmUser && $crmUser->hasAnyListId($condition)) {
                return true;
            } else{
                return false;
            }
        } else {
            if($crmUser && $crmUser->hasAnyTagId($condition)) {
                return true;
            } else{
                return false;
            }
        }
    }
}
