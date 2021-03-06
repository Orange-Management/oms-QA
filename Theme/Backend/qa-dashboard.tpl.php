<?php
/**
 * Orange Management
 *
 * PHP Version 8.0
 *
 * @package   Modules\QA
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://orange-management.org
 */
declare(strict_types=1);

use Modules\Media\Models\NullMedia;
use phpOMS\Uri\UriFactory;

/** @var \Modules\QA\Modles\QAQuestion[] $questions */
$questions = $this->getData('questions');

/** @var \Modules\QA\Modles\QAApp[] $apps */
$apps = $this->getData('apps');

echo $this->getData('nav')->render(); ?>

<div class="row">
    <div class="col-xs-12 box">
        <select>
            <option value="0"><?= $this->getHtml('All'); ?>
            <?php foreach ($apps as $app) : ?>
                <option value="<?= $app->getId(); ?>"><?= $app->name; ?>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<div class="row">
    <div class="col-xs-12">
        <?php foreach ($questions as $question) : ?>
        <section class="portlet qa qa-list">
            <div class="portlet-body">
                <div class="row middle-xs">
                    <div class="counter-area">
                        <div class="counter-container">
                            <span class="counter score<?= $this->printHtml($question->hasAccepted() ? ' done' : ' open'); ?>"><?= $question->getAnswerCount(); ?></span>
                            <span class="text">Answers</span>
                        </div>
                        <div class="counter-container">
                            <span class="counter"><?= $question->getVoteScore(); ?></span>
                            <span class="text">Score</span>
                        </div>
                    </div>
                    <div class="title">
                        <a href="<?= UriFactory::build('{/prefix}qa/question?{?}&id=' . $question->getId()); ?>"><?= $this->printHtml($question->name); ?></a>
                    </div>
                </div>
            </div>
            <div class="portlet-foot qa-portlet-foot">
                <div class="tag-list">
                    <?php $tags = $question->getTags(); foreach ($tags as $tag) : ?>
                        <span class="tag"><?= $tag->icon !== null ? '<i class="' . $this->printHtml($tag->icon ?? '') . '"></i>' : ''; ?><?= $this->printHtml($tag->getL11n()); ?></span>
                    <?php endforeach; ?>
                </div>

                <a class="account-info" href="<?= UriFactory::build('{/prefix}profile/single?{?}&id=' . $question->createdBy->getId()); ?>">
                    <span class="name content"><?= $this->printHtml($question->createdBy->account->name2); ?> <?= $this->printHtml($question->createdBy->account->name1); ?></span>
                    <?php if ($question->createdBy->image !== null && !($question->createdBy->image instanceof NullMedia)) : ?>
                        <img width="40px" alt="<?= $this->getHtml('AccountImage', '0', '0'); ?>" loading="lazy" src="<?= UriFactory::build('{/prefix}' . $question->createdBy->image->getPath()); ?>">
                    <?php endif; ?>
                </a>
            </div>
        </section>
        <?php endforeach; ?>
    </div>
</div>
