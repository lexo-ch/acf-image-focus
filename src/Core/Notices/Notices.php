<?php

namespace LEXO\AcfIF\Core\Notices;

use LEXO\AcfIF\Core\Notices\Notice;

class Notices
{
    private static array $notices = [];

    private static $allowed_html = [
        'p'      => [],
        'a'      => [
            'href' => [],
            'rel'  => [],
        ],
        'em'     => [],
        'strong' => [],
        'br'     => [],
    ];

    public function run()
    {
        add_action('admin_notices', [$this, 'permalinkStructureNoticeMessage']);
    }


    public function add(Notice $notice)
    {
        self::$notices[] = $notice;
    }


    public function permalinkStructureNoticeMessage()
    {
        if (empty(self::$notices)) {
            return;
        }

        ob_start();

        foreach (self::$notices as $key => $notice) {
            $akey = $key + 1;

            $classes = [
                "notice-nr-{$akey}",
                "notice",
                "acfif-notice",
                "notice-" . $notice->getType()
            ];

            if ($notice->isDismissible() === true) {
                $classes[] = 'is-dismissible';
            } ?>

            <div class="<?php echo implode(' ', $classes); ?>">
                <?php echo wpautop(wp_kses($notice->getMessage(), self::$allowed_html)); ?>
            </div>
        <?php }

        echo ob_get_clean();
    }
}
