<?php
	namespace PHPSTORM_META {
	/** @noinspection PhpUnusedLocalVariableInspection */
	/** @noinspection PhpIllegalArrayKeyTypeInspection */
	$STATIC_METHOD_TYPES = [

		\D('') => [
			'Post' instanceof Admin\Model\PostModel,
			'Mongo' instanceof Think\Model\MongoModel,
			'View' instanceof Think\Model\ViewModel,
			'Dept' instanceof Admin\Model\DeptModel,
			'Validate' instanceof Admin\Model\ValidateModel,
			'Adv' instanceof Think\Model\AdvModel,
			'Auto' instanceof Admin\Model\AutoModel,
			'Media' instanceof Admin\Model\MediaModel,
			'Tool' instanceof Admin\Model\ToolModel,
			'Relation' instanceof Think\Model\RelationModel,
			'Lostpass' instanceof Admin\Model\LostpassModel,
			'User' instanceof Admin\Model\UserModel,
			'City' instanceof Admin\Model\CityModel,
			'Merge' instanceof Think\Model\MergeModel,
		],
	];
}