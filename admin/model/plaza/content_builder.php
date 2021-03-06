<?php
class ModelPlazaContentBuilder extends Model
{
    public function addContent($data) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "plaza_content SET status = '" . (int)$data['status'] . "', sort_order = '" . (int)$data['sort_order'] . "', elements = '" . serialize($data['elements']) . "'");

        $content_id = $this->db->getLastId();

        foreach ($data['content_description'] as $language_id => $value) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "plaza_content_description SET content_id = '" . (int)$content_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
        }

        return $content_id;
    }

    public function editContent($content_id, $data) {
        $this->db->query("UPDATE " . DB_PREFIX . "plaza_content SET status = '" . (int)$data['status'] . "', elements = '" . serialize($data['elements']) . "' WHERE content_id = '" . (int)$content_id . "'");

        $this->db->query("DELETE FROM " . DB_PREFIX . "plaza_content_description WHERE content_id = '" . (int)$content_id . "'");

        foreach ($data['content_description'] as $language_id => $value) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "plaza_content_description SET content_id = '" . (int)$content_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
        }
    }

    public function deleteContent($content_id) {
        $this->db->query("DELETE FROM " . DB_PREFIX . "plaza_content WHERE content_id = '" . (int)$content_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "plaza_content_description WHERE content_id = '" . (int)$content_id . "'");
    }

    public function getContent($content_id) {
        $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "plaza_content pc LEFT JOIN " . DB_PREFIX . "plaza_content_description pcd ON (pc.content_id = pcd.content_id) WHERE pc.content_id = '" . (int)$content_id . "' AND pcd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

        return $query->row;
    }

    public function getContentDescription($content_id) {
        $content_description_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "plaza_content_description WHERE content_id = '" . (int)$content_id . "'");

        foreach ($query->rows as $result) {
            $content_description_data[$result['language_id']] = array(
                'name'             => $result['name']
            );
        }

        return $content_description_data;
    }

    public function getContents($data = array()) {
        $sql = "SELECT * FROM " . DB_PREFIX . "plaza_content pc LEFT JOIN " . DB_PREFIX . "plaza_content_description pcd ON (pc.content_id = pcd.content_id) WHERE pcd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

        $sql .= " GROUP BY pc.content_id";

        $sort_data = array(
            'pcd.name',
            'pc.status',
            'pc.sort_order'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY pcd.name";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getTotalContents($data = array()) {
        $sql = "SELECT COUNT(DISTINCT pc.content_id) AS total FROM " . DB_PREFIX . "plaza_content pc LEFT JOIN " . DB_PREFIX . "plaza_content_description pcd ON (pc.content_id = pcd.content_id)";

        $sql .= " WHERE pcd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

        $query = $this->db->query($sql);

        return $query->row['total'];
    }

    public function setup() {
        $this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "plaza_content` (
			    `content_id` INT(11) NOT NULL AUTO_INCREMENT,
	            `sort_order` INT(11) NOT NULL DEFAULT '0',
	            `status` TINYINT(1) NOT NULL DEFAULT '0',
	            `elements` text NOT NULL DEFAULT '',
	        PRIMARY KEY (`content_id`)
		) DEFAULT COLLATE=utf8_general_ci;");

        $this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "plaza_content_description` (
			    `content_id` INT(11) NOT NULL,
                `language_id` INT(11) NOT NULL,
                `name` VARCHAR(255) NOT NULL,
            PRIMARY KEY (`content_id`, `language_id`)
		) DEFAULT COLLATE=utf8_general_ci;");
    }
}
