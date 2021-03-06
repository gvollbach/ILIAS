<?php

/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Block/classes/class.ilBlockGUI.php';

/**
 * Portfolio block for PD
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ilCtrl_IsCalledBy ilPDPortfolioBlockGUI: ilColumnGUI
 */
class ilPDPortfolioBlockGUI extends ilBlockGUI
{
	/**
	 * @var ilSetting
	 */
	protected $settings;

	static $block_type = 'pdportf';
	protected $default_portfolio = 0;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		global $DIC;

		$this->lng = $DIC->language();
		$this->ctrl = $DIC->ctrl();
		$this->settings = $DIC->settings();
		$this->user = $DIC->user();

		parent::__construct();

		$this->setLimit(5);
	}

	/**
	 * @inheritdoc
	 */
	public function getBlockType(): string 
	{
		return self::$block_type;
	}

	/**
	 * @inheritdoc
	 */
	protected function isRepositoryObject(): bool 
	{
		return false;
	}

	/**
	 * Get Screen Mode for current command.
	 */
	static function getScreenMode()
	{
		switch($_GET['cmd'])
		{
			case '...':
				return IL_SCREEN_CENTER;
				break;

			default:
				return IL_SCREEN_SIDE;
				break;
		}
	}

	/**
	 * Execute command
	 */
	public function executeCommand()
	{
		$ilCtrl = $this->ctrl;

		$cmd = $ilCtrl->getCmd('getHTML');

		return $this->$cmd();
	}

	/**
	 * Execute command
	 */
	public function getHTML()
	{
		$lng = $this->lng;
		$ilCtrl = $this->ctrl;
		$ilSetting = $this->settings;
		$ilUser = $this->user;

		if (!$ilSetting->get('user_portfolios'))
		{
			return '';
		}

		include_once("./Modules/Portfolio/classes/class.ilObjPortfolio.php");
		$this->default_portfolio = ilObjPortfolio::getDefaultPortfolio($ilUser->getId());

		$lng->loadLanguageModule("prtf");
		$this->setTitle($lng->txt('prtf_tab_portfolios'));
		$this->addBlockCommand($ilCtrl->getLinkTargetByClass(array("ilDashboardGUI", "ilportfoliorepositorygui"), ""),
			$lng->txt("prtf_manage_portfolios"));
		$this->addBlockCommand($ilCtrl->getLinkTargetByClass(array("ilDashboardGUI", "ilportfoliorepositorygui", "ilobjportfoliogui"), "create"),
			$lng->txt("prtf_add_portfolio"));

		$html = parent::getHTML();
		return $html;
	}

	/**
	 * Fill data section
	 */
	public function fillDataSection()
	{
		$ilUser = $this->user;

		include_once "Modules/Portfolio/classes/class.ilObjPortfolio.php";
		$data = ilObjPortfolio::getPortfoliosOfUser($ilUser->getId());
		$this->setData($data);

		if(count($this->getData()) > 0)
		{
			$this->setRowTemplate("tpl.pd_portf_block_row.html", "Modules/Portfolio");
			parent::fillDataSection();
		}
		else
		{
			$this->setEnableNumInfo(false);
			$this->setDataSection($this->getOverview());
		}
	}

	/**
	 * get flat bookmark list for personal desktop
	 */
	public function fillRow($p)
	{
		$ilCtrl = $this->ctrl;
		$lng = $this->lng;

		$ilCtrl->setParameterByClass("ilobjportfoliogui", "prt_id", $p["id"]);
		$this->tpl->setVariable("HREF", $ilCtrl->getLinkTargetByClass(array("ilDashboardGUI", "ilportfoliorepositorygui", "ilobjportfoliogui"), "preview"));
		$this->tpl->setVariable("TITLE", trim($p["title"]));

		if ($this->default_portfolio == $p["id"])
		{
			// #16490
			$this->tpl->setVariable("DESC", $lng->txt("prtf_default_portfolio"));
		}

		$ilCtrl->setParameterByClass("ilobjportfoliogui", "prt_id", "");
	}

	/**
	 * Get overview.
	 */
	protected function getOverview()
	{
		$lng = $this->lng;
		$ilCtrl = $this->ctrl;

		if (count($this->getData()) == 0)
		{
			return '<div class="small"><a href="'.
				$ilCtrl->getLinkTargetByClass(array("ildashboardgui", "ilportfoliorepositorygui", "ilobjportfoliogui"), "create").
				'">'.$lng->txt("prtf_add_portfolio").'</a></div>';
		}
		else
		{
			$t = (count($this->getData()) == 1)
				? $lng->txt("obj_prtf")
				: $lng->txt("prtf_portfolios");
			return '<div class="small"><a href="'.
				$ilCtrl->getLinkTargetByClass(array("ildashboardgui", "ilportfoliorepositorygui"), "").
				'">'.((int)count($this->getData()))." ".$t."</a></div>";
		}
	}

}
?>