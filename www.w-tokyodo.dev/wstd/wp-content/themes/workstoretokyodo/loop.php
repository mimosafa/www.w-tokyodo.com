<?php

if ( !( is_division() && is_page_top() ) )
	the_title( '<h2>', '</h2>' );

the_content();
