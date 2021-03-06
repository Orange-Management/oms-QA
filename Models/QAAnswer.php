<?php
/**
 * Orange Management
 *
 * PHP Version 8.0
 *
 * @package   Modules\QA\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://orange-management.org
 */
declare(strict_types=1);

namespace Modules\QA\Models;

use Modules\Profile\Models\NullProfile;
use Modules\Profile\Models\Profile;

/**
 * Answer class.
 *
 * @package Modules\QA\Models
 * @license OMS License 1.0
 * @link    https://orange-management.org
 * @since   1.0.0
 */
class QAAnswer implements \JsonSerializable
{
    /**
     * ID.
     *
     * @var int
     * @since 1.0.0
     */
    protected int $id = 0;

    /**
     * Status.
     *
     * @var int
     * @since 1.0.0
     */
    private int $status = QAAnswerStatus::ACTIVE;

    /**
     * Answer.
     *
     * @var string
     * @since 1.0.0
     */
    public string $answer = '';

    /**
     * Answer raw.
     *
     * @var string
     * @since 1.0.0
     */
    public string $answerRaw = '';

    /**
     * Question
     *
     * @var QAQuestion
     * @since 1.0.0
     */
    public QAQuestion $question;

    /**
     * Is accepted answer.
     *
     * @var bool
     * @since 1.0.0
     */
    public bool $isAccepted = false;

    /**
     * Created by.
     *
     * @var Profile
     * @since 1.0.0
     */
    public Profile $createdBy;

    /**
     * Created at.
     *
     * @var \DateTimeImmutable
     * @since 1.0.0
     */
    public \DateTimeImmutable $createdAt;

    /**
     * Votes.
     *
     * @var array
     * @since 1.0.0
     */
    private array $votes = [];

    /**
     * Media files
     *
     * @var array
     * @since 1.0.0
     */
    protected array $media = [];

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable('now');
        $this->createdBy = new NullProfile();
        $this->question  = new NullQAQuestion();
    }

    /**
     * Get id.
     *
     * @return int Model id
     *
     * @since 1.0.0
     */
    public function getId() : int
    {
        return $this->id;
    }

    /**
     * Get the answer
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getAnswer() : string
    {
        return $this->answer;
    }

    /**
     * Set the answer
     *
     * @param string $answer Answer
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setAnswer(string $answer) : void
    {
        $this->answer = $answer;
    }

    /**
     * Get the question
     *
     * @return QAQuestion
     *
     * @since 1.0.0
     */
    public function getQuestion() : QAQuestion
    {
        return $this->question;
    }

    /**
     * Set the question
     *
     * @param QAQuestion $question Question
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setQuestion(QAQuestion $question) : void
    {
        $this->question = $question;
    }

    /**
     * Get the status
     *
     * @return int
     *
     * @since 1.0.0
     */
    public function getStatus() : int
    {
        return $this->status;
    }

    /**
     * Set the status
     *
     * @param int $status Status
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setStatus(int $status) : void
    {
        $this->status = $status;
    }

    /**
     * Set the answer as accepted
     *
     * @param bool $accepted Accepted
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setAccepted(bool $accepted) : void
    {
        $this->isAccepted = $accepted;
    }

    /**
     * Get the total vote score
     *
     * @return int
     *
     * @since 1.0.0
     */
    public function getVoteScore() : int
    {
        $score = 0;
        foreach ($this->votes as $vote) {
            $score += $vote->score;
        }

        return $score;
    }

    /**
     * Get the vote score from an account
     *
     * @param int $account Account id
     *
     * @return int
     *
     * @since 1.0.0
     */
    public function getAccountVoteScore(int $account) : int
    {
        foreach ($this->votes as $vote) {
            if ($vote->createdBy->getId() === $account) {
                return $vote->score;
            }
        }

        return 0;
    }

    /**
     * Get all media
     *
     * @return Media[]
     *
     * @since 1.0.0
     */
    public function getMedia() : array
    {
        return $this->media;
    }

    /**
     * Add media
     *
     * @param Media $media Media to add
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function addMedia(Media $media) : void
    {
        $this->media[] = $media;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize() : array
    {
        return [];
    }
}
