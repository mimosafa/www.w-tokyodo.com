<?php
get_header();
$cf7s = array(
	'event' => array(
		'id' => 173,
		'tab' => 'イベントフード',
		'headline' => 'イベントにおける飲食提供全般のお問い合わせ'
	),
	'lunch' => array(
		'id' => 175,
		'tab' => 'ランチスペース展開',
		'headline' => '空スペースへのネオ屋台村誘致のお問い合わせ'
	),
	'promotion' => array(
		'id' => 176,
		'tab' => 'プロモーション',
		'headline' => '移動販売車やネオ屋台村を利用したプロモーションのお問い合わせ',
		'description' => 'ひとで賑わうネオ屋台村。オフィス街でのプロモーション展開ではOL、ビジネスマン、とターゲットを絞ったプロモーションが可能です。<br>また、当社では屋外での食品提供が可能なインフラが整っているので、ネオ屋台村のスペースに限らず、様々な場所でのサンプリング実施も各方面で好評いただいています。<br>より効果的なプロモーションのお手伝いをおまかせください！'
	),
	'join' => array(
		'id' => 174,
		'tab' => 'ネオ屋台村登録希望',
		'headline' => 'ネオ屋台村への出店希望のお問い合わせ',
		'description' => '現在、登録飽和状態のため、商材としてケバブ、及びカレーを提供されている方の登録をストップさせていただいております。<br>ネオ屋台村でランチ、及びイベント出店をさせて頂くにあたって、スペースオーナー、イベント主催者、そしてご利用者のみなさまの要望を汲み取っていく中でより多種多様なメニューを提示することが求められることをご理解の上、ご了承のほどよろしくお願い致します。'
	),
	'car' => array(
		'id' => 178,
		'tab' => '移動販売車のご相談',
		'headline' => 'メンテナンス・カスタマイズ・購入・売却・レンタルなど、移動販売車についてのお問い合わせ'
	),
	'ponte' => array(
		'id' => 179,
		'tab' => '飲食店舗展開',
		'headline' => 'フードコートなど飲食店展開のお問い合わせ'
	),
	'media' => array(
		'id' => 177,
		'tab' => '撮影・取材協力',
		'headline' => '移動販売車での撮影協力依頼、弊社事業の取材などのお問い合わせ'
	),
	'other' => array(
		'id' => 171,
		'tab' => 'その他',
		'headline' => 'ご意見・ご要望・ご質問など、その他のお問い合わせ'
	)
);
?>
<h2><i class="fa fa-envelope"></i> お問い合わせ</h2>
<div class="alert alert-info fade in">
<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
下記の<?php echo count( $cf7s ); ?>項目の中から、お問い合わせの内容を選択してください。
</div>
<ul class="nav nav-tabs" id="wstd-contacts">
<?php
foreach ( $cf7s as $key => $arg ) {
	printf( '<li><a href="#%s" data-toggle="tooltip" data-original-title="%s" class="%s">%s</a></li>', $key, $arg['headline'], $key, $arg['tab'] );
	echo "\n";
} ?>
</ul>
<div class="tab-content">
<?php
foreach ( $cf7s as $key => $arg ) { ?>
<div class="tab-pane" id="<?php echo $key; ?>">
<h3><?php echo $arg['headline']; ?></h3><?php
	if ( isset( $arg['description'] ) && !empty( $arg['description'] ) ) { ?>
<div class="panel panel-info">
<div class="panel-body">
<?php echo $arg['description']; ?>
</div>
</div><?php
	}
	echo do_shortcode( '[contact-form-7 id="' . $arg['id'] . '"]' ); ?>
</div><?php
} ?>
</div>
<?php
add_action( 'wp_footer', function() { ?>
<script>
  (function($) {
    $('#wstd-contacts a').tooltip({placement: 'bottom'});

    var active = location.hash ? '#wstd-contacts a[href="' + location.hash + '"]' : '';
    location.hash = '';
    if (active)
      $(active).tab('show');

    var alrt = $('.alert');

    $('#wstd-contacts a').click(function(e) {
      e.preventDefault();
      $(this).tab('show');
      alrt.alert('close');
    });
  })(jQuery);
</script>
<?php
}, 99 );
get_footer();
