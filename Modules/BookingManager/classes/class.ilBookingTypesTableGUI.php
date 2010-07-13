<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * List booking types (for booking pool)
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com> 
 * @version $Id$
 *
 * @ingroup ModulesBookingManager
 */
class ilBookingTypesTableGUI extends ilTable2GUI
{
	/**
	 * Constructor
	 * @param	object	$a_parent_obj
	 * @param	string	$a_parent_cmd
	 * @param	int		$a_ref_id
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_ref_id)
	{
		global $ilCtrl, $lng, $ilAccess, $lng, $ilObjDataCache;

		$this->ref_id = $a_ref_id;
		$this->setId("bktp");

		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->setLimit(9999);
		
		if ($ilAccess->checkAccess('write', '', $this->ref_id))
		{
			$this->addCommandButton('create', $this->lng->txt('book_add_type'));
		}

		$this->addColumn($this->lng->txt("title"), "title");
		$this->addColumn($this->lng->txt("book_no_of_objects"));
		$this->addColumn($this->lng->txt("actions"));

		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
		$this->setRowTemplate("tpl.booking_type_row.html", "Modules/BookingManager");
		$this->initFilter();

		$this->getItems($ilObjDataCache->lookupObjId($this->ref_id), $this->getCurrentFilter());

		// remove items which cannot be booked for "normal" users
		if(!$ilAccess->checkAccess('write', '', $this->ref_id))
		{
			foreach($this->row_data as $idx => $row)
			{
				if($row['counter'] == 0)
			    {
					unset($this->row_data[$idx]);
				}
			}
		}
	}

	/**
	* Init filter
	*/
	function initFilter()
	{
		global $lng;

		/*
		$item = $this->addFilterItemByMetaType("country", ilTable2GUI::FILTER_TEXT, true);
		$this->filter["country"] = $item->getValue();
		 */
	}

	/**
	 * Get current filter settings
	 * @return	array
	 */
	function getCurrentFilter()
	{

	}
	
	/**
	 * Build summary item rows for given object and filter(s)
	 *
	 * @param	int	$a_pool_id (aka parent obj id)
	 */
	function getItems($a_pool_id)
	{
		include_once 'Modules/BookingManager/classes/class.ilBookingType.php';
		$data = ilBookingType::getList($a_pool_id);
		
		$this->setMaxCount(sizeof($data));
		$this->setData($data);
	}

	/**
	 * Fill table row
	 * @param	array	$a_set
	 */
	protected function fillRow($a_set)
	{
		global $lng, $ilAccess, $ilCtrl;

	    $this->tpl->setVariable("TXT_TITLE", $a_set["title"]);
	    $this->tpl->setVariable("VALUE_OBJECTS_NO", $a_set["counter"]);

		$ilCtrl->setParameter($this->parent_obj, 'type_id', $a_set['booking_type_id']);
		$ilCtrl->setParameterByClass('ilBookingObjectGUI', 'type_id', $a_set['booking_type_id']);

		$this->tpl->setCurrentBlock('item_command');
		if($a_set["counter"] > 0)
		{
			$this->tpl->setVariable('HREF_COMMAND', $ilCtrl->getLinkTarget($this->parent_obj, 'book'));
			$this->tpl->setVariable('TXT_COMMAND', $lng->txt('book_book'));
			$this->tpl->parseCurrentBlock();
		}

		if ($ilAccess->checkAccess('write', '', $this->ref_id))
		{
			$this->tpl->setVariable('HREF_COMMAND', $ilCtrl->getLinkTargetByClass('ilBookingObjectGUI', 'render'));
			$this->tpl->setVariable('TXT_COMMAND', $lng->txt('book_list_items'));
			$this->tpl->parseCurrentBlock();

			$this->tpl->setVariable('HREF_COMMAND', $ilCtrl->getLinkTarget($this->parent_obj, 'confirmDelete'));
			$this->tpl->setVariable('TXT_COMMAND', $lng->txt('delete'));
			$this->tpl->parseCurrentBlock();

			$this->tpl->setVariable('HREF_COMMAND', $ilCtrl->getLinkTarget($this->parent_obj, 'edit'));
			$this->tpl->setVariable('TXT_COMMAND', $lng->txt('edit'));
			$this->tpl->parseCurrentBlock();
		}
	}
}
?>
