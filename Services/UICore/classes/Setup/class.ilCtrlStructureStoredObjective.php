<?php declare(strict_types=1);

use ILIAS\Setup;

class ilCtrlStructureStoredObjective implements Setup\Objective
{
	const TABLE_CLASSFILES = "ctrl_classfile";
	const TABLE_CALLS = "ctrl_calls";

	/**
	 * @var ilCtrlStructureReader
	 */
	protected $ctrl_reader;

	public function __construct(\ilCtrlStructureReader $ctrl_reader)
	{
		$this->ctrl_reader = $ctrl_reader;
	}

	/**
	 * @inheritdoc
	 */
	public function getHash(): string
	{
		return hash("sha256", self::class);
	}

	/**
	 * @inheritdoc
	 */
	public function getLabel(): string
	{
		return "ilCtrl-structure is read and stored.";
	}

	/**
	 * @inheritdoc
	 */
	public function isNotable(): bool
	{
		return false;
	}

	/**
	 * @inheritdoc
	 */
	public function getPreconditions(Setup\Environment $environment): array
	{
		$config = $environment->getConfigFor('database');
		return [
			new \ilDatabaseExistsObjective($config)
		];
	}

	/**
	 * @inheritdoc
	 */
	public function achieve(Setup\Environment $environment): Setup\Environment
	{
		$db = $environment->getResource(Setup\Environment::RESOURCE_DATABASE);
		if (! $db) {
			throw new \UnachievableException("Need DB to store control-structure");
		}

		$reader = $this->ctrl_reader->withDB($db);
		$reader->executed = false;
		$reader->readStructure(true, ".");
		return $environment;
	}
}
