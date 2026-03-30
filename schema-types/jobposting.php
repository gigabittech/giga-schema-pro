<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Giga_SP_Type_JobPosting' ) ) {
	class Giga_SP_Type_JobPosting extends Giga_SP_Schema_Base {
		public function get_schema(): array {
			$post = $this->get_post();
			if ( ! $post || ! is_a( $post, 'WP_Post' ) ) return [];

			return [
				'@context' => 'https://schema.org/',
				'@type' => 'JobPosting',
				'title' => get_the_title( $post ),
				'description' => wp_strip_all_tags( $post->post_excerpt ?: $post->post_content ),
				'datePosted' => get_the_date( 'c', $post ),
				'validThrough' => get_post_meta( $post->ID, '_job_valid_through', true ) ?: '',
				'employmentType' => get_post_meta( $post->ID, '_job_employment_type', true ) ?: 'FULL_TIME',
				'hiringOrganization' => [
					'@type' => 'Organization',
					'name' => $this->get_site_name(),
					'sameAs' => home_url(),
				],
				'jobLocation' => [
					'@type' => 'Place',
					'address' => [
						'@type' => 'PostalAddress',
						'addressLocality' => get_post_meta( $post->ID, '_job_locality', true ) ?: '',
						'addressRegion' => get_post_meta( $post->ID, '_job_region', true ) ?: '',
						'addressCountry' => get_post_meta( $post->ID, '_job_country', true ) ?: '',
					]
				]
			];
		}
	}

}
