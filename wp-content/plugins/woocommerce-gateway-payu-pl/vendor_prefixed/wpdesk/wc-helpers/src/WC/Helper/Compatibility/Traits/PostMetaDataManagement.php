<?php

namespace WGPayuVendor\WPDesk\WC\Helper\Compatibility\Traits;

trait PostMetaDataManagement
{
    /** @var int */
    protected $post_id;
    /** @var array */
    protected $meta_data = [];
    /**
     * Save data to the database.
     *
     * @return int order ID
     */
    public function save()
    {
        if (!empty($this->meta_data) && \count($this->meta_data) > 0) {
            foreach ($this->meta_data as $name => $value) {
                \update_post_meta($this->post_id, $name, $value);
            }
        }
        return $this->post_id;
    }
    /**
     * @param string $meta_key
     * @param bool $single
     *
     * @return mixed
     */
    public function get_post_meta($meta_key, $single = \false)
    {
        if (isset($this->meta_data[$meta_key])) {
            return $this->meta_data[$meta_key];
        } else {
            return \get_post_meta($this->post_id, $meta_key, $single);
        }
    }
    /**
     * @param string $name
     * @param string $value
     */
    public function set_post_meta($meta_key, $value)
    {
        $this->meta_data[$meta_key] = $value;
        return \true;
    }
}
