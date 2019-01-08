<?php
/**
 * Created by Roquie.
 * E-mail: roquie0@gmail.com
 * GitHub: Roquie
 * Date: 2019-01-08
 */

namespace Roquie\CircleSdk;

use JsonException;
use Roquie\CircleSdk\Exception\CircleException;

final class CircleCI
{
    private const BASE_URI = 'https://circleci.com/api/v1.1/';
    public const VCS_GITHUB = 'github';
    public const VCS_BITBUCKET = 'bitbucket';

    /**
     * @var array|false|string|null
     */
    private $token;

    /**
     * @var string
     */
    private $vcsType;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $project;

    /**
     * @var string
     */
    private $branch = 'master';


    /**
     * CircleCI constructor.
     *
     * @param string|null $token
     * @param string $vcsType
     */
    public function __construct(?string $token = null, string $vcsType = self::VCS_GITHUB)
    {
        $this->token = is_null($token) ? getenv('CIRCLECI_TOKEN') : $token;
        $this->vcsType = $vcsType;
    }

    /**
     * @param string $vcsType
     * @return CircleCI
     */
    public function setVcsType(string $vcsType): CircleCI
    {
        $this->vcsType = $vcsType;

        return $this;
    }

    /**
     * @param string $username
     * @return CircleCI
     */
    public function setUsername(string $username): CircleCI
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @param string $project
     * @return CircleCI
     */
    public function setProject(string $project): CircleCI
    {
        $this->project = $project;

        return $this;
    }

    /**
     * @param string $branch
     * @return CircleCI
     */
    public function setBranch(string $branch): CircleCI
    {
        $this->branch = $branch;

        return $this;
    }

    /**
     * https://circleci.com/docs/api/#trigger-a-new-job
     * @param array $parameters
     * @param string|null $tag
     * @param int|null $parallel
     * @return mixed
     * @throws \Roquie\CircleSdk\Exception\CircleException
     */
    public function triggerNewJob(array $parameters = [], ?string $tag = null, ?int $parallel = null)
    {
        $array = [];
        if ($parameters) {
            $array['build_parameters'] = $parameters;
        }

        if ($tag) {
            $array['tag'] = $tag;
        }

        if ($parallel) {
            $array['parallel'] = $parallel;
        }

        return $this->request('POST', $this->buildProjectUri(), $array);
    }

    /**
     * Curl uses because while I'm use guzzle/guzzle package
     * CircleCI API returned Ruby Hash (sic!!!@) instead of correct json. WHAT!?
     *
     * @param string $method
     * @param string $uri
     * @param array $body
     * @return mixed
     * @throws \Roquie\CircleSdk\Exception\CircleException
     */
    private function request(string $method, string $uri = '', ?array $body = null)
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => self::BASE_URI . $uri,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                'Authorization: Basic ' . base64_encode($this->token . ':'),
                'Content-Type: application/json',
            ],
        ]);

        if ($body) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($body));
        }

        $response = curl_exec($curl);
        $error = curl_error($curl);
        $no = curl_errno($curl);

        curl_close($curl);

        if ($response === false) {
            throw new CircleException($error, $no);
        }

//        try {
//            $results = json_decode($response, true, JSON_THROW_ON_ERROR);
//        } catch (JsonException $e) {
//            throw new CircleException($e->getMessage(), $e->getCode(), $e);
//        }

        return json_decode($response, true);
    }

    /**
     * @param string $suffix
     * @return string
     */
    private function buildProjectUri(string $suffix = ''): string
    {
        return rtrim("project/{$this->vcsType}/{$this->username}/{$this->project}/{$suffix}", '/') . "/tree/{$this->branch}";
    }
}
