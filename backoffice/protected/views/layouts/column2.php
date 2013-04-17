<?php $this->beginContent('//layouts/main'); ?>

<div class="row-fluid">
	<div class="span2">	
	<?php
		$this->beginWidget('MyWidget', array(
			'title'=>$this->menu_title,
		));
		
		$this->widget('bootstrap.widgets.BootMenu', array(
    		'type'=>'list',
    		'items'=>$this->menu,
		));		
		$this->endWidget();
	?>
	</div>

	<div class="span10">	
		<?php echo $content; ?>		
	</div>
</div>
<?php $this->endContent(); ?>