<?php
/**
 * Enqueue google maps api for Map of Office
 */
add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_script( 'google-maps', 'http://maps.google.com/maps/api/js?sensor=false' );
} );

/**
 * header image
 */
add_action( 'wstd_header_image', function() { ?>
<div class="wstd-header-image" style="background-image:url(<?php echo get_stylesheet_directory_uri() . '/img/great-founder-darker.jpg'; ?>);">
<p id="kokokara">全てはココから始まった…<br><small>- 昭和38年頃、晴海埠頭にて</small></p>
</div><?php
} );

get_header(); ?>
<div class="container wstd-section" id="wstd-home-philosophy">
<div class="row">
<div class="col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2">
<h2 class="h1">イベント成功の鍵を握るのは、そこを訪れる人々の満足度にある…</h2>
<p>ワークストア・トウキョウドゥは「イベント成功の鍵を握るのは、そこを訪れる人々の満足度にある」を創業以来のポリシーとし、お客様第一主義で歩んでまいりました。
イベント参加の回を重ねるごとに、その思いは揺るぎのない確信へと変わっていったように思います。<p>
<p>ワークストア・トウキョウドゥの提供する「イベント向けフードサービス」は、様々なイベント主催企業様やご担当者様、そして何よりもイベントに来られた来場者様の笑顔と共に成長してまいりました。</p>
<p>これからも、その「来場者様の笑顔」を大切にし、皆様のお力になれるよう努力する所存です。イベントをご企画する際は、飲食エリアを是非トウキョウドゥにお任せ下さい。</p>
</div>
</div>
</div>

<div id="direct">
<div class="container-fluid wstd-home-image">
</div>
<div class="wstd-home-description linked-box">
<div class="container">
<div class="row">
<div class="col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2">
<h2 class="h1">食を通じた楽しいひとときをお届けします</h2>
<p>屋外、屋内、ケータリンカー、テント・ブースなど条件を問わず飲食エリアを制作・運営いたします。</p>
<p>1963年創業の直営店は大型ケータリングカーを所有し、様々な食の要望に柔軟に対応可能です。<br>
またコラボレーションフード、オフィシャルバー、ケータリングサービスなどイベントの食にまつわることは全てワークストア・トウキョウドゥにお任せ下さい。</p>
<p>どんなイベントにも「食」を通じた楽しいひとときをお届けします。</p>
</div>
</div>
</div>
<a href="/direct"><i class="fa fa-angle-double-right"></i></a>
</div>
</div>

<div id="neostall">
<div class="container-fluid wstd-home-image">
</div>
<div class="wstd-home-description linked-box">
<div class="container">
<div class="row">
<div class="col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2">
<h2 class="h1">オフィス街のランチお助け隊</h2>
<p>個性豊かなキッチンカーが集まり、世界各国の様々なメニューやオリジナルメニューを手作り感たっぷりにリーズナブルな価格で提供し「食」を通じて「賑わい」を創る。それが「ネオ屋台村」です。</p>
<p>出店スペースに合わせて、登録台数300店以上のネオ屋台から台数・メニューに合わせてスケジューリングします。ネオ屋台村では、目の前で行われる調理・盛付、そこに生まれる店主との会話を大切に利用者のニコニコを集めてスペースの賑わいを創出いたします。</p>
<p>スペース（地域）の活性化・イベント会場のフードエリアの賑わい創り・オフィスワーカーの憩いの場創りにご利用頂いております。人が集う「ネオ屋台村」は、サービスプロモーションにもご利用頂けます。</p>
</div>
</div>
</div>
<a href="/neostall"><i class="fa fa-angle-double-right"></i></a>
</div>
</div>

<div id="neoponte">
<div class="container-fluid wstd-home-image">
</div>
<div class="wstd-home-description linked-box">
<div class="container">
<div class="row">
<div class="col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2">
<h2 class="h1">千葉県活性化飲食店「千葉らぁ麺」</h2>
<p>千葉らぁ麺の目的は食を通じて『千葉県の地域活性化』する事にあります。
『千葉県の新たな魅力』をアリオ蘇我から全国に向けて発信し、飲食店ながらも千葉の素材（人・食材・モノ）を活かし新たに千葉の魅力を発信する基地としての役割を目的として展開致します。</p>
</div>
</div>
</div>
<a href="/neoponte"><i class="fa fa-angle-double-right"></i></a>
</div>
</div>

<div id="sharyobu">
<div class="container-fluid wstd-home-image">
</div>
<div class="wstd-home-description linked-box">
<div class="container">
<div class="row">
<div class="col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2">
<h2 class="h1">移動販売車の事なら何でもお任せを</h2>
<p>ケータリングカー、キッチンカー、フードトラックなどなど、呼び名はさまざまですが、近年多くのひとびと、業界に認知され、街なかで目にすることも多くなった移動販売車。しかし、これら特殊な車両の製作、メンテナンスについては専門的な知識と経験を要します。</p>
<p>ワークストア・トウキョウドゥ 車両部では長年にわたって培った豊富な経験から、様々なニーズにあわせてお一人お一人、そして一台一台に親切、柔軟な対応を心がけています。<br>
車検・修理・改造などメンテナンスやカスタマイズに関すること、オリジナルのケータリングカー製作や中古ケータリングカーの販売、専用厨房パーツやラッピングなどもご相談ください。要望に沿った"愛着のある一台"のためにお手伝いいたします。
また、様々なイベントに対応するケータリングカーのレンタルもおこなっています。移動販売車の事なら何でもお任せを！</p>
</div>
</div>
</div>
<a href="/sharyobu"><i class="fa fa-angle-double-right"></i></a>
</div>
</div>

<div id="company" class="wstd-home-description">
<div class="container">
<div class="row">
<div class="col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2">
<h2 class="h1">会社概要</h2>
<?php echo do_shortcode( '[company_attribute]' ); ?>
</div>
</div>
</div>
</div>

<div id="gmap" style="width: 100%; height:500px;"></div><?php

/**
 * Draw Google Maps
 */
add_action( 'wp_footer', function() { ?>
<script>
var latlng = new google.maps.LatLng(35.560561, 139.707181);
var myOptions = {
  zoom: 18,
  center: latlng,
  mapTypeId: google.maps.MapTypeId.ROADMAP,
  scrollwheel: false,
  draggable: false
};
var map = new google.maps.Map(document.getElementById('gmap'), myOptions);
var marker = new google.maps.Marker({
  position: latlng,
  map: map,
  title: 'Workstore Tokyo Do OFFICE'
});
</script>
<?php
} );

get_footer(); ?>
