<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Giga_SP_Types' ) ) {
	class Giga_SP_Types {
		public static function get_all_types() {
			return [
				'free' => [
					'Article', 'WebPage', 'Product', 'BreadcrumbList', 'Organization', 
					'Person', 'WebSite', 'FAQ', 'HowTo', 'LocalBusiness'
				],
				'pro' => [
					'Review', 'AggregateRating', 'Offer', 'Event', 'Course', 
					'Recipe', 'VideoObject', 'SoftwareApplication', 'Book', 'JobPosting', 
					'Service', 'MedicalCondition', 'RealEstateListing', 'CollectionPage', 
					'ItemList', 'SpeakableSpecification', 'Custom', 
				]
			];
		}
	}
}
