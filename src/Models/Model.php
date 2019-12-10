<?php

namespace BluestormDesign\TeamworkCrm\Models;

use BluestormDesign\TeamworkCrm\Rest\Model as RestModel;
use Exception;

abstract class Model extends RestModel
{
    /**
     * @param $id
     * @param null $params
     *
     * @return \TeamWorkPm\Response\Model
     * @throws \Exception
     */
    public function getAll($params = null)
    {
        return $this->rest->get("$this->action", $params);
    }

    /**
     * @param $id
     * @param null $params
     *
     * @return \TeamWorkPm\Response\Model
     * @throws \Exception
     */
    public function get($id, $params = null)
    {
        $id = (int) $id;
        if ($id <= 0) {
            throw new Exception('Invalid param id');
        }

        return $this->rest->get("$this->action/$id", $params);
    }

    /**
     * @param array $data
     * @return int
     */
    public function insert(array $data)
    {
        return $this->rest->post($this->action, $data);
    }

    /**
     * @param array $data
     *
     * @return bool
     * @throws \Exception
     */
    public function update(array $data)
    {
        $id = empty($data['id']) ? 0: (int) $data['id'];
        if ($id <= 0) {
            throw new Exception('Required field id');
        }
        return $this->rest->put("$this->action/$id", $data);
    }

    /**
     * @param array $data
     *
     * @return bool|int
     * @throws \Exception
     */
    final public function save(array $data)
    {
        return array_key_exists('id', $data) ?
            $this->update($data):
            $this->insert($data);
    }

    /**
     * @param int $id
     *
     * @return bool
     * @throws \Exception
     */
    public function delete($id)
    {
        $id = (int) $id;
        if ($id <= 0) {
            throw new Exception('Invalid param id');
        }
        return $this->rest->delete("$this->action/$id");
    }
}
