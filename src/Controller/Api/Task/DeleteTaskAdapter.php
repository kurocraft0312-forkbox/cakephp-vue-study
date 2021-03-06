<?php
declare(strict_types=1);

namespace App\Controller\Api\Task;

use Cake\ORM\Locator\LocatorAwareTrait;
use Cas\Domain\Exception\DomainNotFoundException;
use Cas\Domain\Exception\DomainSystemException;
use Cas\Domain\Model\Task;
use Cas\Domain\Model\TaskId;
use Cas\UseCase\Task\DeleteTaskCommandPort;

class DeleteTaskAdapter implements DeleteTaskCommandPort
{
    use LocatorAwareTrait;

    /**
     * @param \Cas\Domain\Model\TaskId $id id
     * @return \Cas\Domain\Model\Task
     */
    public function delete(TaskId $id): Task
    {
        $Tasks = $this->getTableLocator()->get('Tasks');

        /** @var \App\Model\Entity\Task|null $taskEntity */
        $taskEntity = $Tasks->find()->where(['id' => $id->asString()])->first();
        if (is_null($taskEntity)) {
            throw new DomainNotFoundException("削除する情報がありませんでした。 task id={$id->asString()}");
        }

        if (!$Tasks->delete($taskEntity, ['atomic' => false])) {
            throw new DomainSystemException("削除できませんでした。 task id={$id->asString()}");
        }

        return $taskEntity->toModel();
    }
}
