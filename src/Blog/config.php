<?php

use App\Blog\BlogWidget;

use function \Di\add;
use function \Di\get;

return [
	'blog.prefix' => '/blog',
	'admin.widgets' => add([
		get(BlogWidget::class)
	])
];
