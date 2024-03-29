<?php

namespace Mirko\Newsletter\Domain\Model\RecipientList;

use Mirko\Newsletter\Tools;

/**
 * Recipient List using Frontend User stored on a given pages
 */
class FePages extends GentleSql
{
    /**
     * fePages
     *
     * @var string
     */
    protected $fePages;

    /**
     * Setter for fePages
     *
     * @param string $fePages fePages
     */
    public function setFePages($fePages)
    {
        $this->fePages = $fePages;
    }

    /**
     * Getter for fePages
     *
     * @return string fePages
     */
    public function getFePages()
    {
        return $this->fePages;
    }

    /**
     * Returns the tablename to work with
     *
     * @return string
     */
    protected function getTableName()
    {
        return 'fe_users';
    }

    public function init()
    {
        $config = explode(',', $this->getFePages());
        $config[] = -1;
        $config = array_filter($config);

        $this->data = Tools::executeRawDBQuery(
            'SELECT DISTINCT email,name,address,telephone,fax,username,fe_users.title,zip,city,country,www,company,pages.title AS pages_title
				FROM pages
				INNER JOIN fe_users ON pages.uid = fe_users.pid
				WHERE pages.uid IN (' . implode(',', $config) . ")
				AND email != ''
				AND pages.deleted = 0
				AND pages.hidden = 0
				AND fe_users.disable = 0
				AND fe_users.deleted = 0
				AND tx_newsletter_bounce < 10"
        );
    }
}
