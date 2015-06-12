<?php

/**
 * This file is part of Zenify
 * Copyright (c) 2012 Tomas Votruba (http://tomasvotruba.cz)
 */

namespace Zenify\DoctrineFixtures\Alice;

use Doctrine\ORM\EntityManagerInterface;
use Nelmio\Alice\Fixtures\Loader;
use Nette\Utils\Finder;
use Zenify\DoctrineFixtures\Contract\Alice\AliceLoaderInterface;
use Zenify\DoctrineFixtures\Exception\MissingSourceException;


class AliceLoader implements AliceLoaderInterface
{

	/**
	 * @var EntityManagerInterface
	 */
	private $entityManager;

	/**
	 * @var Loader
	 */
	private $aliceLoader;


	public function __construct(EntityManagerInterface $entityManager, Loader $aliceLoader)
	{
		$this->entityManager = $entityManager;
		$this->aliceLoader = $aliceLoader;
	}


	/**
	 * {@inheritdoc}
	 */
	public function load($sources)
	{
		if ( ! is_array($sources)) {
			$sources = [$sources];
		}

		$entities = [];
		foreach ($sources as $source) {
			$newEntities = $this->loadEntitiesFromSource($source);
			$entities = array_merge($entities, $newEntities);
		}

		$this->entityManager->flush();

		return $entities;
	}


	/**
	 * @param $source
	 * @return object[]
	 * @throws MissingSourceException
	 */
	private function loadEntitiesFromSource($source)
	{
		if (is_file($source)) {
			return $this->loadFromFile($source);

		} elseif (is_dir($source)) {
			return $this->loadFromDirectory($source);

		} else {
			throw new MissingSourceException(
				sprintf('Source "%s" was not found.', $source)
			);
		}
	}


	/**
	 * @param string $path
	 * @return object[]
	 */
	private function loadFromFile($path)
	{
		$entities = $this->aliceLoader->load($path);
		foreach ($entities as $entity) {
			$this->entityManager->persist($entity);
		}
		return $entities;
	}


	/**
	 * {@inheritdoc}
	 */
	private function loadFromDirectory($path)
	{
		$files = [];
		foreach (Finder::find(['*.neon', '*.yaml', '*.yml'])->from($path) as $file) {
			$files[] = $file;
		}
		return $this->load($files);
	}

}
