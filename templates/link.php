<?php
	if (! isset($app)) exit;

	$app->stash->title = $app->dict()->tr('Link');

	include 'templates/_header.php';
?>
<div id="link" class="container">
	<div id="control">
		<a href="<?php echo $app->uri('index') ?>">
			<i class="fa fa-chevron-left" aria-hidden="true"></i>
			<?php echo $app->dict()->tr('To Home Page') ?>
		</a>
	</div>
	<div class="row">
		<div class="col-sm-6">
			<div class="well">
				<h4 class="title ellipsis nowrap">lava->uri([path|route [, data [, append]]]) : uri</h4>
				<div class="essense">
<pre>
echo $app->uri();
# <?php echo htmlspecialchars(var_export($app->uri(), TRUE)); ?>
</pre>

<pre>
echo $app->uri('');
# <?php echo htmlspecialchars(var_export($app->uri(''), TRUE)); ?>
</pre>

<pre>
echo $app->uri('bar');
# <?php echo htmlspecialchars(var_export($app->uri('bar'), TRUE)); ?>
</pre>

<pre>
echo $app->uri('/bar', array('arg' => '1#2'));
# <?php echo htmlspecialchars(var_export($app->uri('/bar', array('arg' => '1#2')), TRUE)); ?>
</pre>

<pre>
echo $app->uri('bar', 'arg=1#2', TRUE);
# <?php echo htmlspecialchars(var_export($app->uri('bar', 'arg=1#2', TRUE), TRUE)); ?>
</pre>
				</div>
			</div>
		</div>
		<div class="col-sm-6">
			<div class="well">
				<h4 class="title ellipsis nowrap">lava->url([path|route [, data [, append [, subdomain]]]]) : url</h4>
				<div class="essense">
<pre>
echo $app->url();
# <?php echo htmlspecialchars(var_export($app->url(), TRUE)); ?>
</pre>

<pre>
echo $app->url('');
# <?php echo htmlspecialchars(var_export($app->url(''), TRUE)); ?>
</pre>

<pre>
echo $app->url('bar');
# <?php echo htmlspecialchars(var_export($app->url('bar'), TRUE)); ?>
</pre>

<pre>
echo $app->url('/bar', array('arg' => '1#2'));
# <?php echo htmlspecialchars(var_export($app->url('/bar', array('arg' => '1#2')), TRUE)); ?>
</pre>

<pre>
echo $app->url('bar', 'arg=1#2', TRUE);
# <?php echo htmlspecialchars(var_export($app->url('bar', 'arg=1#2', TRUE), TRUE)); ?>
</pre>

<pre>
echo $app->url('bar', 'arg=1#2', TRUE, 'subdomain');
# <?php echo htmlspecialchars(var_export($app->url('bar', 'arg=1#2', TRUE, 'subdomain'), TRUE)); ?>
</pre>

				</div>
			</div>
		</div>
	</div>
	<div class="well">
		<h4 class="title ellipsis nowrap">lava->host([scheme [, subdomain]]) : host</h4>
		<div class="essense">
<pre>
echo $app->host();
# <?php echo htmlspecialchars(var_export($app->host(), TRUE)); ?>
</pre>

<pre>
echo $app->host('ftp');
# <?php echo htmlspecialchars(var_export($app->host('ftp'), TRUE)); ?>
</pre>

<pre>
echo $app->host(TRUE);
# <?php echo htmlspecialchars(var_export($app->host(TRUE), TRUE)); ?>
</pre>

<pre>
echo $app->host('https', 'safe');
# <?php echo htmlspecialchars(var_export($app->host('https', 'safe'), TRUE)); ?>
</pre>
		</div>
	</div>
	<div class="well">
		<h4 class="title ellipsis nowrap">lava->pub([node, ...]) : pub</h4>
		<div class="essense">
<pre>
echo $app->pub();
# <?php echo htmlspecialchars(var_export($app->pub(), TRUE)); ?>
</pre>

<pre>
echo $app->pub('js/main.js');
# <?php echo htmlspecialchars(var_export($app->pub('js/main.js'), TRUE)); ?>
</pre>
		</div>
	</div>
</div>
<?php
	include 'templates/_footer.php';
?>
