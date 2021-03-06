<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/WebServices/ECS/interfaces/interface.ilECSCommandQueueHandler.php';
include_once './Services/WebServices/ECS/classes/class.ilECSSetting.php';
include_once './Services/WebServices/ECS/classes/class.ilECSParticipantSetting.php';


/**
 * Description of class
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilECSEnrolmentStatusCommandQueueHandler implements ilECSCommandQueueHandler
{
	private $server = null;
	private $mid = 0;

	/**
	 * @var ilRecommendedContentManager
	 */
	protected $recommended_content_manager;

	/**
	 * Constructor
	 */
	public function __construct(ilECSSetting $server)
	{
		$this->server = $server;
		$this->recommended_content_manager = new ilRecommendedContentManager();
	}
	
	/**
	 * Get server
	 * @return ilECSServerSetting
	 */
	public function getServer()
	{
		return $this->server;
	}
	
	/**
	 * Get mid
	 * @return type
	 */
	public function getMid()
	{
		return $this->mid;
	}
	
	/**
	 * Check if course allocation is activated for one recipient of the 
	 * @param ilECSSetting $server
	 * @param type $a_content_id
	 */
	public function checkAllocationActivation(ilECSSetting $server, $a_content_id)
	{
		
	}


	/**
	 * Handle create
	 * @param ilECSSetting $server
	 * @param type $a_content_id
	 */
	public function handleCreate(ilECSSetting $server, $a_content_id)
	{
		try
		{
			include_once './Services/WebServices/ECS/classes/Connectors/class.ilECSEnrolmentStatus.php';
			include_once './Services/WebServices/ECS/classes/Connectors/class.ilECSEnrolmentStatusConnector.php';
			$enrolment_con = new ilECSEnrolmentStatusConnector($server);
			$status = $enrolment_con->getEnrolmentStatus($a_content_id);
			$GLOBALS['DIC']['ilLog']->write(__METHOD__.': '.print_r($status,TRUE));
			$GLOBALS['DIC']['ilLog']->write(__METHOD__.': '.$status->getPersonIdType());
			$GLOBALS['DIC']['ilLog']->write(__METHOD__.': '.$status->getPersonId());
			switch($status->getPersonIdType())
			{
				case ilECSEnrolmentStatus::ID_UID:
					$id_arr = ilUtil::parseImportId($status->getPersonId());
					$GLOBALS['DIC']['ilLog']->write(__METHOD__.': Handling status change to '.$status->getStatus().' for user '.$id_arr['id']);
					$this->doUpdate($id_arr['id'],$status);
					break;
					
					
					
				default:
					$GLOBALS['DIC']['ilLog']->write(__METHOD__.': not implemented yes: person id type: '.$status->getPersonIdType());
					break;
			}
			
		}
		catch (ilECSConnectorException $e)
		{
			$GLOBALS['DIC']['ilLog']->write(__METHOD__.': Enrolment status change failed with messsage: '.$e->getMessage());
		}
		return TRUE;
	}

	/**
	 * Handle delete
	 * @param ilECSSetting $server
	 * @param type $a_content_id
	 */
	public function handleDelete(ilECSSetting $server, $a_content_id)
	{
		// nothing todo
		return true;
	}

	/**
	 * Handle update
	 * @param ilECSSetting $server
	 * @param type $a_content_id
	 */
	public function handleUpdate(ilECSSetting $server, $a_content_id)
	{
		// Shouldn't happen
		return true;
	}
	
	
	/**
	 * Perform update
	 * @param type $a_content_id
	 * @param type $course
	 */
	protected function doUpdate($a_usr_id, ilECSEnrolmentStatus $status)
	{
		include_once './Services/WebServices/ECS/classes/class.ilECSImport.php';
		$obj_ids = ilECSImport::lookupObjIdsByContentId($status->getId());
		$obj_id = end($obj_ids);
		$ref_ids = ilObject::_getAllReferences($obj_id);
		$ref_id = end($ref_ids);
		
		
		if(!$ref_id)
		{
			// Remote object not found
			return TRUE;
		}
		
		switch($status->getStatus())
		{
			case ilECSEnrolmentStatus::STATUS_PENDING:
				// nothing todo in the moment: maybe send mail
				break;
				
			case ilECSEnrolmentStatus::STATUS_ACTIVE:
				$GLOBALS['DIC']['ilLog']->write(__METHOD__.': Add recommended content: '.$a_usr_id.' '.$ref_id.' '.$obj_id);
				// deactivated for now, see discussion at
				// https://docu.ilias.de/goto_docu_wiki_wpage_5620_1357.html
				//$this->recommended_content_manager->addObjectRecommendation($a_usr_id, $ref_id);
				break;
			
			case ilECSEnrolmentStatus::STATUS_ACCOUNT_DEACTIVATED:
			case ilECSEnrolmentStatus::STATUS_DENIED:
			case ilECSEnrolmentStatus::STATUS_REJECTED:
			case ilECSEnrolmentStatus::STATUS_UNSUBSCRIBED:
				$GLOBALS['DIC']['ilLog']->write(__METHOD__.': Remove recommended content: '.$a_usr_id.' '.$ref_id.' '.$obj_id);
				$this->recommended_content_manager->removeObjectRecommendation($a_usr_id, $ref_id);
				break;
		}
		return TRUE;
	}
	

}
?>
