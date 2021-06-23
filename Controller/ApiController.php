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

namespace Modules\QA\Controller;

use Modules\Admin\Models\NullAccount;
use Modules\Profile\Models\Profile;
use Modules\QA\Models\NullQAAnswerVote;
use Modules\QA\Models\NullQACategory;
use Modules\QA\Models\NullQAQuestion;
use Modules\QA\Models\NullQAQuestionVote;
use Modules\QA\Models\QAAnswer;
use Modules\QA\Models\QAAnswerMapper;
use Modules\QA\Models\QAAnswerStatus;
use Modules\QA\Models\QAAnswerVote;
use Modules\QA\Models\QAAnswerVoteMapper;
use Modules\QA\Models\QACategory;
use Modules\QA\Models\QACategoryL11n;
use Modules\QA\Models\QACategoryL11nMapper;
use Modules\QA\Models\QACategoryMapper;
use Modules\QA\Models\QAQuestion;
use Modules\QA\Models\QAQuestionMapper;
use Modules\QA\Models\QAQuestionStatus;
use Modules\QA\Models\QAQuestionVote;
use Modules\QA\Models\QAQuestionVoteMapper;
use Modules\Tag\Models\NullTag;
use phpOMS\Message\Http\HttpResponse;
use phpOMS\Message\Http\RequestStatusCode;
use phpOMS\Message\NotificationLevel;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\Model\Message\FormValidation;
use phpOMS\Utils\Parser\Markdown\Markdown;

/**
 * Task class.
 *
 * @package Modules\QA
 * @license OMS License 1.0
 * @link    https://orange-management.org
 * @since   1.0.0
 */
final class ApiController extends Controller
{
    /**
     * Api method to create a question
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiQuestionUpdate(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
    }

    /**
     * Api method to create a question
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiQACategoryUpdate(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
    }

    /**
     * Api method to create a question
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiAnswerUpdate(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
    }

    /**
     * Api method to create a question
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiQAQuestionCreate(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        if (!empty($val = $this->validateQAQuestionCreate($request))) {
            $response->set('qa_question_create', new FormValidation($val));
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        $question = $this->createQAQuestionFromRequest($request, $response, $data);
        $this->createModel($request->header->account, $question, QAQuestionMapper::class, 'question', $request->getOrigin());
        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Question', 'Question successfully created.', $question);
    }

    /**
     * Method to create question from request.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return QAQuestion Returns the created question from the request
     *
     * @since 1.0.0
     */
    public function createQAQuestionFromRequest(RequestAbstract $request, ResponseAbstract $response, $data = null) : QAQuestion
    {
        $mardkownParser = new Markdown();

        $question              = new QAQuestion();
        $question->name        = (string) $request->getData('title');
        $question->questionRaw = (string) $request->getData('plain');
        $question->question    = Markdown::parse((string) ($request->getData('plain') ?? ''));
        $question->setLanguage((string) $request->getData('language'));
        $question->setCategory(new NullQACategory((int) $request->getData('category')));
        $question->setStatus((int) $request->getData('status'));
        $question->createdBy = new Profile(new NullAccount($request->header->account));

        if (!empty($tags = $request->getDataJson('tags'))) {
            foreach ($tags as $tag) {
                if (!isset($tag['id'])) {
                    $request->setData('title', $tag['title'], true);
                    $request->setData('color', $tag['color'], true);
                    $request->setData('icon', $tag['icon'] ?? null, true);
                    $request->setData('language', $tag['language'], true);

                    $internalResponse = new HttpResponse();
                    $this->app->moduleManager->get('Tag')->apiTagCreate($request, $internalResponse, $data);
                    $question->addTag($internalResponse->get($request->uri->__toString())['response']);
                } else {
                    $question->addTag(new NullTag((int) $tag['id']));
                }
            }
        }

        return $question;
    }

    /**
     * Validate question create request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool> Returns the validation array of the request
     *
     * @since 1.0.0
     */
    private function validateQAQuestionCreate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['title'] = empty($request->getData('title')))
            || ($val['plain'] = empty($request->getData('plain')))
            || ($val['language'] = empty($request->getData('language')))
            || ($val['category'] = empty($request->getData('category')))
            || ($val['status'] = (
                $request->getData('status') !== null
                && !QAQuestionStatus::isValidValue((int) $request->getData('status'))
            ))
        ) {
            return $val;
        }

        return [];
    }

    /**
     * Api method to create a answer
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiQAAnswerCreate(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        if (!empty($val = $this->validateQAAnswerCreate($request))) {
            $response->set('qa_answer_create', new FormValidation($val));
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        $answer = $this->createQAAnswerFromRequest($request);
        $this->createModel($request->header->account, $answer, QAAnswerMapper::class, 'answer', $request->getOrigin());
        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Answer', 'Answer successfully created.', $answer);
    }

    /**
     * Method to create answer from request.
     *
     * @param RequestAbstract $request Request
     *
     * @return QAAnswer Returns the created answer from the request
     *
     * @since 1.0.0
     */
    public function createQAAnswerFromRequest(RequestAbstract $request) : QAAnswer
    {
        $mardkownParser = new Markdown();

        $answer              = new QAAnswer();
        $answer->answerRaw   = (string) $request->getData('plain');
        $answer->answer      = Markdown::parse((string) ($request->getData('plain') ?? ''));
        $answer->question    = new NullQAQuestion((int) $request->getData('question'));
        $answer->isAccepted  = false;
        $answer->setStatus((int) $request->getData('status'));
        $answer->createdBy = new NullAccount($request->header->account);

        return $answer;
    }

    /**
     * Validate answer create request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool> Returns the validation array of the request
     *
     * @since 1.0.0
     */
    private function validateQAAnswerCreate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['plain'] = empty($request->getData('plain')))
            || ($val['question'] = empty($request->getData('question')))
            || ($val['status'] = (
                $request->getData('status') !== null
                && !QAAnswerStatus::isValidValue((int) $request->getData('status'))
            ))
        ) {
            return $val;
        }

        return [];
    }

    /**
     * Api method to create a answer
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiChangeAnsweredStatus(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        $old = clone QAAnswerMapper::get((int) $request->getData('id'));
        $new = $this->updateAnsweredStatusFromRequest($request);
        $this->updateModel($request->header->account, $old, $new, QAAnswerMapper::class, 'answer', $request->getOrigin());
        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Answer', 'Answer successfully updated.', $new);
    }

    /**
     * Method to create category from request.
     *
     * @param RequestAbstract $request Request
     *
     * @return QAAnswer
     *
     * @since 1.0.0
     */
    public function updateAnsweredStatusFromRequest(RequestAbstract $request) : QAAnswer
    {
        $answer             = QAAnswerMapper::get((int) $request->getData('id'));
        $answer->isAccepted = $request->getData('accepted', 'bool') ?? false;

        return $answer;
    }

    /**
     * Api method to create a category
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiQACategoryCreate(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        if (!empty($val = $this->validateQACategoryCreate($request))) {
            $response->set('qa_category_create', new FormValidation($val));
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        $category = $this->createQACategoryFromRequest($request);
        $category->setL11n($request->getData('name'), $request->getData('language'));
        $this->createModel($request->header->account, $category, QACategoryMapper::class, 'category', $request->getOrigin());

        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Category', 'Category successfully created.', $category);
    }

    /**
     * Method to create category from request.
     *
     * @param RequestAbstract $request Request
     *
     * @return QACategory Returns the created category from the request
     *
     * @since 1.0.0
     */
    public function createQACategoryFromRequest(RequestAbstract $request) : QACategory
    {
        $category = new QACategory();
        //$category->setApp(new NullQAApp((int) ($request->getData('app') ?? 1)));

        if ($request->getData('parent') !== null) {
            $category->parent = new NullQACategory((int) $request->getData('parent'));
        }

        return $category;
    }

    /**
     * Validate category create request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool> Returns the validation array of the request
     *
     * @since 1.0.0
     */
    private function validateQACategoryCreate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['name'] = empty($request->getData('name')))) {
            return $val;
        }

        return [];
    }

    /**
     * Validate tag l11n create request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @since 1.0.0
     */
    private function validateQACategoryL11nCreate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['name'] = empty($request->getData('name')))
            || ($val['category'] = empty($request->getData('category')))
        ) {
            return $val;
        }

        return [];
    }

    /**
     * Api method to create tag localization
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiQACategoryL11nCreate(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        if (!empty($val = $this->validateQACategoryL11nCreate($request))) {
            $response->set('qa_category_l11n_create', new FormValidation($val));
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        $l11nQACategory = $this->createQACategoryL11nFromRequest($request);
        $this->createModel($request->header->account, $l11nQACategory, QACategoryL11nMapper::class, 'qa_category_l11n', $request->getOrigin());

        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Localization', 'Category localization successfully created', $l11nQACategory);
    }

    /**
     * Method to create tag localization from request.
     *
     * @param RequestAbstract $request Request
     *
     * @return QACategoryL11n
     *
     * @since 1.0.0
     */
    private function createQACategoryL11nFromRequest(RequestAbstract $request) : QACategoryL11n
    {
        $l11nQACategory = new QACategoryL11n();
        $l11nQACategory->setCategory((int) ($request->getData('category') ?? 0));
        $l11nQACategory->setLanguage((string) (
            $request->getData('language') ?? $request->getLanguage()
        ));
        $l11nQACategory->name = (string) ($request->getData('name') ?? '');

        return $l11nQACategory;
    }

    /**
     * Api method to change question vote
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiChangeQAQuestionVote(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        if (!empty($val = $this->validateQuestionVote($request))) {
            $response->set('qa_question_vote', new FormValidation($val));
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        $questionVote = QAQuestionVoteMapper::findVote((int) $request->getData('id'), $request->header->account);

        if ($questionVote === false || $questionVote instanceof NullQAQuestionVote || $questionVote === null) {
            $new            = new QAQuestionVote();
            $new->score     = (int) $request->getData('type');
            $new->question  = (int) $request->getData('id');
            $new->createdBy = new NullAccount($request->header->account);

            $this->createModel($request->header->account, $new, QAQuestionVoteMapper::class, 'qa_question_vote', $request->getOrigin());
            $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Vote', 'Sucessfully voted.', $new);
        } else {
            $new        = clone $questionVote;
            $new->score = (int) $request->getData('type');

            $this->updateModel($request->header->account, $questionVote, $new, QAQuestionVoteMapper::class, 'qa_question_vote', $request->getOrigin());
            $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Vote', 'Vote successfully changed.', $new);
        }
    }

    /**
     * Validate question vote request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool> Returns the validation array of the request
     *
     * @since 1.0.0
     */
    private function validateQuestionVote(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['id'] = ($request->getData('id') === null))
            || ($val['type'] = ($request->getData('type', 'int') < -1 || $request->getData('type') > 1))
        ) {
            return $val;
        }

        return [];
    }

    /**
     * Api method to change answer vote
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiChangeQAAnswerVote(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        if (!empty($val = $this->validateAnswerVote($request))) {
            $response->set('qa_answer_vote', new FormValidation($val));
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        $answerVote = QAAnswerVoteMapper::findVote((int) $request->getData('id'), $request->header->account);

        if ($answerVote === false || $answerVote instanceof NullQAAnswerVote || $answerVote === null) {
            $new            = new QAAnswerVote();
            $new->score     = (int) $request->getData('type');
            $new->answer    = (int) $request->getData('id');
            $new->createdBy = new NullAccount($request->header->account);

            $this->createModel($request->header->account, $new, QAAnswerVoteMapper::class, 'qa_answer_vote', $request->getOrigin());
            $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Vote', 'Sucessfully voted.', $new);
        } else {
            $new        = clone $answerVote;
            $new->score = (int) $request->getData('type');

            $this->updateModel($request->header->account, $answerVote, $new, QAAnswerVoteMapper::class, 'qa_answer_vote', $request->getOrigin());
            $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Vote', 'Vote successfully changed.', $new);
        }
    }

    /**
     * Validate answer vote request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool> Returns the validation array of the request
     *
     * @since 1.0.0
     */
    private function validateAnswerVote(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['id'] = ($request->getData('id') === null))
            || ($val['type'] = ($request->getData('type', 'int') < -1 || $request->getData('type') > 1))
        ) {
            return $val;
        }

        return [];
    }
}
