<?php

namespace Database\Seeders;

use App\Models\Sector;
use Illuminate\Database\Seeder;

/**
 * Sector / inspection checklist rows across the workflow stages.
 * Generated from the reviewed dataset. Idempotent — keyed on `key`.
 */
class SectorSeeder extends Seeder
{
    public function run(): void
    {
        $rows = array (
  0 => 
  array (
    'key' => 'evaluation_1__application_completeness__1',
    'label' => 'All required application sections completed',
    'description' => 'Confirm every mandatory field and section of the application form has been filled in.',
    'section' => 'Application Completeness',
    'stage_key' => 'evaluation_1',
    'order' => 1,
  ),
  1 => 
  array (
    'key' => 'evaluation_1__application_completeness__2',
    'label' => 'Supporting documents attached',
    'description' => 'Verify the applicant has attached the documents required at submission.',
    'section' => 'Application Completeness',
    'stage_key' => 'evaluation_1',
    'order' => 2,
  ),
  2 => 
  array (
    'key' => 'evaluation_1__application_completeness__3',
    'label' => 'Requested amount and budget provided',
    'description' => 'Check a clear requested amount and an itemised budget are present.',
    'section' => 'Application Completeness',
    'stage_key' => 'evaluation_1',
    'order' => 3,
  ),
  3 => 
  array (
    'key' => 'evaluation_1__eligibility__1',
    'label' => 'Eligible organisation type',
    'description' => 'Confirm the organisation type qualifies under WRBLO eligibility rules.',
    'section' => 'Eligibility',
    'stage_key' => 'evaluation_1',
    'order' => 4,
  ),
  4 => 
  array (
    'key' => 'evaluation_1__eligibility__2',
    'label' => 'Project within WRBLO funding remit',
    'description' => 'Check the project\'s purpose falls within the causes WRBLO funds.',
    'section' => 'Eligibility',
    'stage_key' => 'evaluation_1',
    'order' => 5,
  ),
  5 => 
  array (
    'key' => 'evaluation_1__eligibility__3',
    'label' => 'Eligible geographic location',
    'description' => 'Verify the project location is within an area WRBLO supports.',
    'section' => 'Eligibility',
    'stage_key' => 'evaluation_1',
    'order' => 6,
  ),
  6 => 
  array (
    'key' => 'evaluation_1__alignment_with_mission__1',
    'label' => 'Aligns with WRBLO vision & mission',
    'description' => 'Assess whether the proposal supports WRBLO\'s stated vision and mission.',
    'section' => 'Alignment with Mission',
    'stage_key' => 'evaluation_1',
    'order' => 7,
  ),
  7 => 
  array (
    'key' => 'evaluation_1__alignment_with_mission__2',
    'label' => 'Clear charitable / social benefit',
    'description' => 'Confirm the project delivers a genuine charitable or social benefit.',
    'section' => 'Alignment with Mission',
    'stage_key' => 'evaluation_1',
    'order' => 8,
  ),
  8 => 
  array (
    'key' => 'evaluation_1__initial_merit__1',
    'label' => 'Proposal is clear and coherent',
    'description' => 'Judge whether the proposal is understandable, logical and well presented.',
    'section' => 'Initial Merit',
    'stage_key' => 'evaluation_1',
    'order' => 9,
  ),
  9 => 
  array (
    'key' => 'evaluation_1__initial_merit__2',
    'label' => 'Stated need is credible',
    'description' => 'Assess whether the described need is believable on a first read.',
    'section' => 'Initial Merit',
    'stage_key' => 'evaluation_1',
    'order' => 10,
  ),
  10 => 
  array (
    'key' => 'evaluation_1__initial_merit__3',
    'label' => 'Outcomes are plausible',
    'description' => 'Consider whether the proposed outcomes seem realistic at a high level.',
    'section' => 'Initial Merit',
    'stage_key' => 'evaluation_1',
    'order' => 11,
  ),
  11 => 
  array (
    'key' => 'evaluation_1__recommendation__1',
    'label' => 'Recommend proceeding to Audit',
    'description' => 'Record whether the application should advance to the PMCU Audit stage.',
    'section' => 'Recommendation',
    'stage_key' => 'evaluation_1',
    'order' => 12,
  ),
  12 => 
  array (
    'key' => 'evaluation_1__recommendation__2',
    'label' => 'Notes / flags for the Audit team',
    'description' => 'Capture any concerns or areas the Audit team should focus on.',
    'section' => 'Recommendation',
    'stage_key' => 'evaluation_1',
    'order' => 13,
  ),
  13 => 
  array (
    'key' => 'audit__contact_details__1',
    'label' => 'Check and scan the registration documents',
    'description' => 'Obtain and scan registration documents showing the group\'s name, address and telephone/fax numbers.',
    'section' => 'Contact Details',
    'stage_key' => 'audit',
    'order' => 1,
  ),
  14 => 
  array (
    'key' => 'audit__contact_details__2',
    'label' => 'Obtain ID for key persons and all PSCs',
    'description' => 'Collect identification for individual key persons and everyone with significant control (PSC).',
    'section' => 'Contact Details',
    'stage_key' => 'audit',
    'order' => 2,
  ),
  15 => 
  array (
    'key' => 'audit__organisation_aims_objectives__1',
    'label' => 'On-camera interviews with all PSCs',
    'description' => 'Conduct on-camera interviews with every PSC using the evaluator\'s scripted questions to confirm a shared vision and mission.',
    'section' => 'Organisation Aims & Objectives',
    'stage_key' => 'audit',
    'order' => 3,
  ),
  16 => 
  array (
    'key' => 'audit__background_of_the_applicant_organisation__1',
    'label' => 'History of similar work',
    'description' => 'Research whether the organisation has previously done work similar to what they are applying for.',
    'section' => 'Background of the Applicant Organisation',
    'stage_key' => 'audit',
    'order' => 4,
  ),
  17 => 
  array (
    'key' => 'audit__background_of_the_applicant_organisation__2',
    'label' => 'Past scale of operations',
    'description' => 'Establish the size and scale the organisation has operated at historically.',
    'section' => 'Background of the Applicant Organisation',
    'stage_key' => 'audit',
    'order' => 5,
  ),
  18 => 
  array (
    'key' => 'audit__background_of_the_applicant_organisation__3',
    'label' => 'Capacity to implement as proposed',
    'description' => 'Judge whether, if funded, the organisation could deliver the project as described given its experience.',
    'section' => 'Background of the Applicant Organisation',
    'stage_key' => 'audit',
    'order' => 6,
  ),
  19 => 
  array (
    'key' => 'audit__evidence_of_need__1',
    'label' => 'Need exists in the community / location',
    'description' => 'Determine whether the proposed work is genuinely needed in the target community or location.',
    'section' => 'Evidence of Need',
    'stage_key' => 'audit',
    'order' => 7,
  ),
  20 => 
  array (
    'key' => 'audit__evidence_of_need__2',
    'label' => 'Proof of need provided',
    'description' => 'Check how the applicant proves the need exists.',
    'section' => 'Evidence of Need',
    'stage_key' => 'audit',
    'order' => 8,
  ),
  21 => 
  array (
    'key' => 'audit__evidence_of_need__3',
    'label' => 'National statistics verified',
    'description' => 'Verify any national statistics the applicant cites.',
    'section' => 'Evidence of Need',
    'stage_key' => 'audit',
    'order' => 9,
  ),
  22 => 
  array (
    'key' => 'audit__evidence_of_need__4',
    'label' => 'Community surveys / feedback checked',
    'description' => 'Check whether quantitative or qualitative surveys or other feedback from the community were carried out.',
    'section' => 'Evidence of Need',
    'stage_key' => 'audit',
    'order' => 10,
  ),
  23 => 
  array (
    'key' => 'audit__evidence_of_need__5',
    'label' => 'Residents\' voice included',
    'description' => 'Review what the proposal reports about what local residents say.',
    'section' => 'Evidence of Need',
    'stage_key' => 'audit',
    'order' => 11,
  ),
  24 => 
  array (
    'key' => 'audit__evidence_of_need__6',
    'label' => 'Extent of need demonstrated',
    'description' => 'Assess whether the applicant has demonstrated the scale and extent of the need.',
    'section' => 'Evidence of Need',
    'stage_key' => 'audit',
    'order' => 12,
  ),
  25 => 
  array (
    'key' => 'audit__what_the_organisation_is_applying_for__1',
    'label' => 'Staffing levels',
    'description' => 'Establish the current and proposed staffing levels.',
    'section' => 'What the Organisation is Applying For',
    'stage_key' => 'audit',
    'order' => 13,
  ),
  26 => 
  array (
    'key' => 'audit__what_the_organisation_is_applying_for__2',
    'label' => 'Funding for staff or resources',
    'description' => 'Clarify whether the request is to fund additional staff or resources.',
    'section' => 'What the Organisation is Applying For',
    'stage_key' => 'audit',
    'order' => 14,
  ),
  27 => 
  array (
    'key' => 'audit__what_the_organisation_is_applying_for__3',
    'label' => 'Addresses societal / social needs',
    'description' => 'Confirm the proposal tackles genuine societal or social needs within the community served.',
    'section' => 'What the Organisation is Applying For',
    'stage_key' => 'audit',
    'order' => 15,
  ),
  28 => 
  array (
    'key' => 'audit__what_the_organisation_is_applying_for__4',
    'label' => 'Measurable problem-solving',
    'description' => 'Determine how WRBLO will know the funding is helping to solve the stated problems.',
    'section' => 'What the Organisation is Applying For',
    'stage_key' => 'audit',
    'order' => 16,
  ),
  29 => 
  array (
    'key' => 'audit__how_the_project_meets_the_criteria__1',
    'label' => 'Best placed to deliver',
    'description' => 'Assess why this organisation is best equipped to deliver the objective.',
    'section' => 'How the Project Meets the Criteria',
    'stage_key' => 'audit',
    'order' => 17,
  ),
  30 => 
  array (
    'key' => 'audit__how_the_project_meets_the_criteria__2',
    'label' => 'Meets all funding criteria',
    'description' => 'Confirm the funding request satisfies every criterion set out.',
    'section' => 'How the Project Meets the Criteria',
    'stage_key' => 'audit',
    'order' => 18,
  ),
  31 => 
  array (
    'key' => 'audit__evaluation_monitoring__1',
    'label' => 'Self-assessment plans',
    'description' => 'Review the practical plans for the applicant self-assessment of the proposal.',
    'section' => 'Evaluation & Monitoring',
    'stage_key' => 'audit',
    'order' => 19,
  ),
  32 => 
  array (
    'key' => 'audit__evaluation_monitoring__2',
    'label' => 'Ongoing evaluation approach',
    'description' => 'Understand how ongoing evaluation will be conducted.',
    'section' => 'Evaluation & Monitoring',
    'stage_key' => 'audit',
    'order' => 20,
  ),
  33 => 
  array (
    'key' => 'audit__evaluation_monitoring__3',
    'label' => 'Ongoing monitoring approach',
    'description' => 'Understand how ongoing monitoring will be performed. — Note to team: WRBLO auditors also act as independent external evaluators in addition to any internal M&E.',
    'section' => 'Evaluation & Monitoring',
    'stage_key' => 'audit',
    'order' => 21,
  ),
  34 => 
  array (
    'key' => 'audit__project_budget__1',
    'label' => 'Budget amendments',
    'description' => 'Identify any amendments to the proposed budget submitted with the application.',
    'section' => 'Project Budget',
    'stage_key' => 'audit',
    'order' => 22,
  ),
  35 => 
  array (
    'key' => 'audit__project_budget__2',
    'label' => 'Fully costed',
    'description' => 'Confirm the application is fully costed and includes every possible expense.',
    'section' => 'Project Budget',
    'stage_key' => 'audit',
    'order' => 23,
  ),
  36 => 
  array (
    'key' => 'audit__project_budget__3',
    'label' => 'Retrospective costs',
    'description' => 'Check whether any costs listed are for retrospective items.',
    'section' => 'Project Budget',
    'stage_key' => 'audit',
    'order' => 24,
  ),
  37 => 
  array (
    'key' => 'audit__project_budget__4',
    'label' => 'Capital build / equipment funding',
    'description' => 'Determine whether funding is required to cover capital build and equipment.',
    'section' => 'Project Budget',
    'stage_key' => 'audit',
    'order' => 25,
  ),
  38 => 
  array (
    'key' => 'audit__project_budget__5',
    'label' => 'Realistic estimates & quotes',
    'description' => 'Verify the costings incorporate realistic estimates and quotes.',
    'section' => 'Project Budget',
    'stage_key' => 'audit',
    'order' => 26,
  ),
  39 => 
  array (
    'key' => 'audit__project_budget__6',
    'label' => 'Salary levels benchmarked',
    'description' => 'Confirm salary levels are commensurate with a recognised pay scale.',
    'section' => 'Project Budget',
    'stage_key' => 'audit',
    'order' => 27,
  ),
  40 => 
  array (
    'key' => 'audit__project_budget__7',
    'label' => 'Basis of cost calculations',
    'description' => 'Understand the basis on which costs have been calculated.',
    'section' => 'Project Budget',
    'stage_key' => 'audit',
    'order' => 28,
  ),
  41 => 
  array (
    'key' => 'audit__project_budget__8',
    'label' => 'Multi-year budgeting',
    'description' => 'Check whether the budget extends beyond a single year.',
    'section' => 'Project Budget',
    'stage_key' => 'audit',
    'order' => 29,
  ),
  42 => 
  array (
    'key' => 'audit__project_budget__9',
    'label' => 'Inflation & cost-of-living accounted for',
    'description' => 'Confirm the budget accounts for inflation and general cost-of-living rises (salaries, electricity, telephone, stationery).',
    'section' => 'Project Budget',
    'stage_key' => 'audit',
    'order' => 30,
  ),
  43 => 
  array (
    'key' => 'audit__project_budget__10',
    'label' => 'Interim price increases',
    'description' => 'If the project starts several months away, consider whether prices will have risen in the interim. — Note to team: WRBLO may accept volunteer hours as an in-kind community contribution counting as match funding.',
    'section' => 'Project Budget',
    'stage_key' => 'audit',
    'order' => 31,
  ),
  44 => 
  array (
    'key' => 'audit__project_sustainability__1',
    'label' => 'Sustainability scenario beyond the grant',
    'description' => 'Determine which applies if funding is needed beyond the WRBLO grant: the project finishes at grant end; secures funding from other sources; generates its own revenue via services/contracts; or is wound down in an orderly manner.',
    'section' => 'Project Sustainability',
    'stage_key' => 'audit',
    'order' => 32,
  ),
  45 => 
  array (
    'key' => 'audit__camera_crew__1',
    'label' => 'Camera crew arranged',
    'description' => 'Confirm a camera crew has been arranged for the required on-camera interviews.',
    'section' => 'Camera Crew',
    'stage_key' => 'audit',
    'order' => 33,
  ),
  46 => 
  array (
    'key' => 'audit__camera_crew__2',
    'label' => 'PSC interview footage captured',
    'description' => 'Verify on-camera interview footage has been captured for all PSCs.',
    'section' => 'Camera Crew',
    'stage_key' => 'audit',
    'order' => 34,
  ),
  47 => 
  array (
    'key' => 'audit__camera_crew__3',
    'label' => 'Footage reviewed & archived',
    'description' => 'Confirm the footage has been reviewed and securely archived against the application.',
    'section' => 'Camera Crew',
    'stage_key' => 'audit',
    'order' => 35,
  ),
  48 => 
  array (
    'key' => 'evaluation_2__review_of_audit_findings__1',
    'label' => 'Audit findings reviewed',
    'description' => 'Review the PMCU Audit team findings and confirm they have been taken into account.',
    'section' => 'Review of Audit Findings',
    'stage_key' => 'evaluation_2',
    'order' => 1,
  ),
  49 => 
  array (
    'key' => 'evaluation_2__review_of_audit_findings__2',
    'label' => 'Outstanding audit concerns resolved',
    'description' => 'Check whether any concerns raised at the Audit stage have been addressed.',
    'section' => 'Review of Audit Findings',
    'stage_key' => 'evaluation_2',
    'order' => 2,
  ),
  50 => 
  array (
    'key' => 'evaluation_2__impact_outcomes__1',
    'label' => 'Outcomes clearly defined & measurable',
    'description' => 'Confirm the project outcomes are specific and measurable.',
    'section' => 'Impact & Outcomes',
    'stage_key' => 'evaluation_2',
    'order' => 3,
  ),
  51 => 
  array (
    'key' => 'evaluation_2__impact_outcomes__2',
    'label' => 'Beneficiaries clearly identified',
    'description' => 'Verify the intended beneficiaries are clearly defined.',
    'section' => 'Impact & Outcomes',
    'stage_key' => 'evaluation_2',
    'order' => 4,
  ),
  52 => 
  array (
    'key' => 'evaluation_2__impact_outcomes__3',
    'label' => 'Scale of impact justified',
    'description' => 'Assess whether the expected scale of impact is credible and well evidenced.',
    'section' => 'Impact & Outcomes',
    'stage_key' => 'evaluation_2',
    'order' => 5,
  ),
  53 => 
  array (
    'key' => 'evaluation_2__value_for_money__1',
    'label' => 'Costs proportionate to outcomes',
    'description' => 'Judge whether the budget is proportionate to the expected outcomes.',
    'section' => 'Value for Money',
    'stage_key' => 'evaluation_2',
    'order' => 6,
  ),
  54 => 
  array (
    'key' => 'evaluation_2__value_for_money__2',
    'label' => 'No unnecessary costs',
    'description' => 'Check the budget for avoidable or unnecessary expenditure.',
    'section' => 'Value for Money',
    'stage_key' => 'evaluation_2',
    'order' => 7,
  ),
  55 => 
  array (
    'key' => 'evaluation_2__deliverability_capacity__1',
    'label' => 'Organisation capacity confirmed',
    'description' => 'Confirm the organisation has the capacity and experience to deliver as proposed.',
    'section' => 'Deliverability & Capacity',
    'stage_key' => 'evaluation_2',
    'order' => 8,
  ),
  56 => 
  array (
    'key' => 'evaluation_2__deliverability_capacity__2',
    'label' => 'Delivery timeframe realistic',
    'description' => 'Assess whether the proposed timeframe is achievable.',
    'section' => 'Deliverability & Capacity',
    'stage_key' => 'evaluation_2',
    'order' => 9,
  ),
  57 => 
  array (
    'key' => 'evaluation_2__deliverability_capacity__3',
    'label' => 'Key risks identified & mitigated',
    'description' => 'Check that implementation risks are identified and have mitigation plans.',
    'section' => 'Deliverability & Capacity',
    'stage_key' => 'evaluation_2',
    'order' => 10,
  ),
  58 => 
  array (
    'key' => 'evaluation_2__scoring_recommendation__1',
    'label' => 'Overall evaluation score',
    'description' => 'Record the overall evaluation score for the application.',
    'section' => 'Scoring & Recommendation',
    'stage_key' => 'evaluation_2',
    'order' => 11,
  ),
  59 => 
  array (
    'key' => 'evaluation_2__scoring_recommendation__2',
    'label' => 'Recommend proceeding to ECO Assessment',
    'description' => 'Record whether the application should advance to ECO Assessment.',
    'section' => 'Scoring & Recommendation',
    'stage_key' => 'evaluation_2',
    'order' => 12,
  ),
  60 => 
  array (
    'key' => 'evaluation_2__scoring_recommendation__3',
    'label' => 'Conditions or recommendations',
    'description' => 'Note any conditions or recommendations to carry forward.',
    'section' => 'Scoring & Recommendation',
    'stage_key' => 'evaluation_2',
    'order' => 13,
  ),
  61 => 
  array (
    'key' => 'eco_assessment__project_feasibility__1',
    'label' => 'Realistic & achievable in timeframe',
    'description' => 'Assess whether the proposed project is realistic and achievable within the stated timeframe.',
    'section' => 'Project Feasibility',
    'stage_key' => 'eco_assessment',
    'order' => 1,
  ),
  62 => 
  array (
    'key' => 'eco_assessment__project_feasibility__2',
    'label' => 'Clear & measurable objectives',
    'description' => 'Confirm the project objectives are clear and measurable.',
    'section' => 'Project Feasibility',
    'stage_key' => 'eco_assessment',
    'order' => 2,
  ),
  63 => 
  array (
    'key' => 'eco_assessment__project_feasibility__3',
    'label' => 'Sufficient delivery capacity',
    'description' => 'Judge whether the organisation has sufficient capacity to deliver the project successfully.',
    'section' => 'Project Feasibility',
    'stage_key' => 'eco_assessment',
    'order' => 3,
  ),
  64 => 
  array (
    'key' => 'eco_assessment__project_feasibility__4',
    'label' => 'Implementation risks identified',
    'description' => 'Check whether any significant implementation risks have been identified.',
    'section' => 'Project Feasibility',
    'stage_key' => 'eco_assessment',
    'order' => 4,
  ),
  65 => 
  array (
    'key' => 'eco_assessment__community_impact__1',
    'label' => 'Measurable positive impact',
    'description' => 'Assess whether the project will create a measurable positive impact within the target community.',
    'section' => 'Community Impact',
    'stage_key' => 'eco_assessment',
    'order' => 5,
  ),
  66 => 
  array (
    'key' => 'eco_assessment__community_impact__2',
    'label' => 'Outcomes clearly defined',
    'description' => 'Confirm the expected outcomes have been clearly defined.',
    'section' => 'Community Impact',
    'stage_key' => 'eco_assessment',
    'order' => 6,
  ),
  67 => 
  array (
    'key' => 'eco_assessment__community_impact__3',
    'label' => 'Beneficiaries clearly identified',
    'description' => 'Verify the intended beneficiaries are clearly identified.',
    'section' => 'Community Impact',
    'stage_key' => 'eco_assessment',
    'order' => 7,
  ),
  68 => 
  array (
    'key' => 'eco_assessment__community_impact__4',
    'label' => 'Addresses a genuine need',
    'description' => 'Check there is evidence the project addresses a genuine community need.',
    'section' => 'Community Impact',
    'stage_key' => 'eco_assessment',
    'order' => 8,
  ),
  69 => 
  array (
    'key' => 'eco_assessment__sustainability__1',
    'label' => 'Benefits continue post-funding',
    'description' => 'Determine whether the project can continue delivering benefits after the funding period.',
    'section' => 'Sustainability',
    'stage_key' => 'eco_assessment',
    'order' => 9,
  ),
  70 => 
  array (
    'key' => 'eco_assessment__sustainability__2',
    'label' => 'Long-term sustainability plan',
    'description' => 'Check the organisation has identified long-term sustainability plans.',
    'section' => 'Sustainability',
    'stage_key' => 'eco_assessment',
    'order' => 10,
  ),
  71 => 
  array (
    'key' => 'eco_assessment__sustainability__3',
    'label' => 'Realistic ongoing requirements',
    'description' => 'Assess whether ongoing operational requirements are realistic.',
    'section' => 'Sustainability',
    'stage_key' => 'eco_assessment',
    'order' => 11,
  ),
  72 => 
  array (
    'key' => 'eco_assessment__resource_assessment__1',
    'label' => 'Resources reasonable for activities',
    'description' => 'Judge whether the requested resources are reasonable for the proposed activities.',
    'section' => 'Resource Assessment',
    'stage_key' => 'eco_assessment',
    'order' => 12,
  ),
  73 => 
  array (
    'key' => 'eco_assessment__resource_assessment__2',
    'label' => 'Budget proportionate to outcomes',
    'description' => 'Confirm the proposed budget is proportionate to the expected outcomes.',
    'section' => 'Resource Assessment',
    'stage_key' => 'eco_assessment',
    'order' => 13,
  ),
  74 => 
  array (
    'key' => 'eco_assessment__resource_assessment__3',
    'label' => 'Unnecessary costs identified',
    'description' => 'Check whether any unnecessary costs have been identified.',
    'section' => 'Resource Assessment',
    'stage_key' => 'eco_assessment',
    'order' => 14,
  ),
  75 => 
  array (
    'key' => 'eco_assessment__recommendation__1',
    'label' => 'Meets WRBLO funding objectives',
    'description' => 'Confirm the project meets WRBLO\'s funding objectives.',
    'section' => 'Recommendation',
    'stage_key' => 'eco_assessment',
    'order' => 15,
  ),
  76 => 
  array (
    'key' => 'eco_assessment__recommendation__2',
    'label' => 'Proceed to Legal Review',
    'description' => 'Record whether the application should proceed to Legal Review.',
    'section' => 'Recommendation',
    'stage_key' => 'eco_assessment',
    'order' => 16,
  ),
  77 => 
  array (
    'key' => 'eco_assessment__recommendation__3',
    'label' => 'Recommendations or conditions',
    'description' => 'Note any recommendations or conditions before approval.',
    'section' => 'Recommendation',
    'stage_key' => 'eco_assessment',
    'order' => 17,
  ),
  78 => 
  array (
    'key' => 'legal_review__organisation_compliance__1',
    'label' => 'Legally registered',
    'description' => 'Confirm the organisation is legally registered.',
    'section' => 'Organisation Compliance',
    'stage_key' => 'legal_review',
    'order' => 1,
  ),
  79 => 
  array (
    'key' => 'legal_review__organisation_compliance__2',
    'label' => 'Statutory documents valid & current',
    'description' => 'Verify all statutory registration documents are valid and current.',
    'section' => 'Organisation Compliance',
    'stage_key' => 'legal_review',
    'order' => 2,
  ),
  80 => 
  array (
    'key' => 'legal_review__organisation_compliance__3',
    'label' => 'No legal issues affecting funding',
    'description' => 'Check for any legal issues that may affect the funding agreement.',
    'section' => 'Organisation Compliance',
    'stage_key' => 'legal_review',
    'order' => 3,
  ),
  81 => 
  array (
    'key' => 'legal_review__documentation__1',
    'label' => 'All mandatory documents provided',
    'description' => 'Confirm all mandatory documents have been provided.',
    'section' => 'Documentation',
    'stage_key' => 'legal_review',
    'order' => 4,
  ),
  82 => 
  array (
    'key' => 'legal_review__documentation__2',
    'label' => 'Documents complete & executed',
    'description' => 'Verify submitted documents are complete and properly executed.',
    'section' => 'Documentation',
    'stage_key' => 'legal_review',
    'order' => 5,
  ),
  83 => 
  array (
    'key' => 'legal_review__documentation__3',
    'label' => 'Additional legal documents required?',
    'description' => 'Determine whether any additional legal documents are required.',
    'section' => 'Documentation',
    'stage_key' => 'legal_review',
    'order' => 6,
  ),
  84 => 
  array (
    'key' => 'legal_review__agreement_review__1',
    'label' => 'Funding agreement prepared correctly',
    'description' => 'Confirm the funding agreement has been prepared correctly.',
    'section' => 'Agreement Review',
    'stage_key' => 'legal_review',
    'order' => 7,
  ),
  85 => 
  array (
    'key' => 'legal_review__agreement_review__2',
    'label' => 'Terms reflect approved funding',
    'description' => 'Check the agreement terms accurately reflect the approved funding.',
    'section' => 'Agreement Review',
    'stage_key' => 'legal_review',
    'order' => 8,
  ),
  86 => 
  array (
    'key' => 'legal_review__agreement_review__3',
    'label' => 'Required clauses included',
    'description' => 'Verify all required clauses are included.',
    'section' => 'Agreement Review',
    'stage_key' => 'legal_review',
    'order' => 9,
  ),
  87 => 
  array (
    'key' => 'legal_review__signature_verification__1',
    'label' => 'Authorised representatives signed',
    'description' => 'Confirm all required authorised representatives have signed the agreement.',
    'section' => 'Signature Verification',
    'stage_key' => 'legal_review',
    'order' => 10,
  ),
  88 => 
  array (
    'key' => 'legal_review__signature_verification__2',
    'label' => 'Electronic signatures verified',
    'description' => 'Verify all electronic signatures have been successfully verified.',
    'section' => 'Signature Verification',
    'stage_key' => 'legal_review',
    'order' => 11,
  ),
  89 => 
  array (
    'key' => 'legal_review__signature_verification__3',
    'label' => 'Signatures legally valid',
    'description' => 'Confirm all signatures are legally valid.',
    'section' => 'Signature Verification',
    'stage_key' => 'legal_review',
    'order' => 12,
  ),
  90 => 
  array (
    'key' => 'legal_review__legal_recommendation__1',
    'label' => 'Satisfies all legal requirements',
    'description' => 'Confirm the application satisfies all legal requirements.',
    'section' => 'Legal Recommendation',
    'stage_key' => 'legal_review',
    'order' => 13,
  ),
  91 => 
  array (
    'key' => 'legal_review__legal_recommendation__2',
    'label' => 'Proceed to Directors / Trustees',
    'description' => 'Record whether the application can proceed to the Board.',
    'section' => 'Legal Recommendation',
    'stage_key' => 'legal_review',
    'order' => 14,
  ),
  92 => 
  array (
    'key' => 'legal_review__legal_recommendation__3',
    'label' => 'Outstanding legal risks',
    'description' => 'Note any outstanding legal risks.',
    'section' => 'Legal Recommendation',
    'stage_key' => 'legal_review',
    'order' => 15,
  ),
  93 => 
  array (
    'key' => 'board_validation__governance_review__1',
    'label' => 'Aligns with vision & mission',
    'description' => 'Confirm the application aligns with WRBLO\'s vision and mission.',
    'section' => 'Governance Review',
    'stage_key' => 'board_validation',
    'order' => 1,
  ),
  94 => 
  array (
    'key' => 'board_validation__governance_review__2',
    'label' => 'Supports strategic objectives',
    'description' => 'Check the proposal supports the organisation\'s strategic objectives.',
    'section' => 'Governance Review',
    'stage_key' => 'board_validation',
    'order' => 2,
  ),
  95 => 
  array (
    'key' => 'board_validation__governance_review__3',
    'label' => 'Governance standards maintained',
    'description' => 'Confirm governance standards are being maintained.',
    'section' => 'Governance Review',
    'stage_key' => 'board_validation',
    'order' => 3,
  ),
  96 => 
  array (
    'key' => 'board_validation__transparency__1',
    'label' => 'All review stages completed',
    'description' => 'Verify every previous review stage has been completed.',
    'section' => 'Transparency',
    'stage_key' => 'board_validation',
    'order' => 4,
  ),
  97 => 
  array (
    'key' => 'board_validation__transparency__2',
    'label' => 'All approvals obtained',
    'description' => 'Confirm all required approvals have been obtained.',
    'section' => 'Transparency',
    'stage_key' => 'board_validation',
    'order' => 5,
  ),
  98 => 
  array (
    'key' => 'board_validation__transparency__3',
    'label' => 'Sufficient supporting evidence',
    'description' => 'Check there is sufficient evidence supporting the funding recommendation.',
    'section' => 'Transparency',
    'stage_key' => 'board_validation',
    'order' => 6,
  ),
  99 => 
  array (
    'key' => 'board_validation__risk_assessment__1',
    'label' => 'Significant risks identified',
    'description' => 'Determine whether the project presents any significant financial, operational or reputational risks.',
    'section' => 'Risk Assessment',
    'stage_key' => 'board_validation',
    'order' => 7,
  ),
  100 => 
  array (
    'key' => 'board_validation__risk_assessment__2',
    'label' => 'Risks adequately addressed',
    'description' => 'Confirm all identified risks have been adequately addressed.',
    'section' => 'Risk Assessment',
    'stage_key' => 'board_validation',
    'order' => 8,
  ),
  101 => 
  array (
    'key' => 'board_validation__risk_assessment__3',
    'label' => 'Additional conditions required?',
    'description' => 'Decide whether additional conditions are required before approval.',
    'section' => 'Risk Assessment',
    'stage_key' => 'board_validation',
    'order' => 9,
  ),
  102 => 
  array (
    'key' => 'board_validation__value_assessment__1',
    'label' => 'Impact justifies investment',
    'description' => 'Judge whether the anticipated impact justifies the proposed investment.',
    'section' => 'Value Assessment',
    'stage_key' => 'board_validation',
    'order' => 10,
  ),
  103 => 
  array (
    'key' => 'board_validation__value_assessment__2',
    'label' => 'Meaningful beneficiary benefits',
    'description' => 'Confirm the funding will create meaningful benefits for the intended beneficiaries.',
    'section' => 'Value Assessment',
    'stage_key' => 'board_validation',
    'order' => 11,
  ),
  104 => 
  array (
    'key' => 'board_validation__value_assessment__3',
    'label' => 'Responsible stewardship of funds',
    'description' => 'Assess whether the proposal demonstrates responsible stewardship of donor funds.',
    'section' => 'Value Assessment',
    'stage_key' => 'board_validation',
    'order' => 12,
  ),
  105 => 
  array (
    'key' => 'board_validation__final_decision__1',
    'label' => 'Approve application',
    'description' => 'Record whether the application should be approved.',
    'section' => 'Final Decision',
    'stage_key' => 'board_validation',
    'order' => 13,
  ),
  106 => 
  array (
    'key' => 'board_validation__final_decision__2',
    'label' => 'Request additional information',
    'description' => 'Record whether additional information should be requested.',
    'section' => 'Final Decision',
    'stage_key' => 'board_validation',
    'order' => 14,
  ),
  107 => 
  array (
    'key' => 'board_validation__final_decision__3',
    'label' => 'Reject application',
    'description' => 'Record whether the application should be rejected.',
    'section' => 'Final Decision',
    'stage_key' => 'board_validation',
    'order' => 15,
  ),
  108 => 
  array (
    'key' => 'dcf_check__identity_verification__1',
    'label' => 'All PSCs identified',
    'description' => 'Confirm all Persons with Significant Control (PSCs) have been identified.',
    'section' => 'Identity Verification',
    'stage_key' => 'dcf_check',
    'order' => 1,
  ),
  109 => 
  array (
    'key' => 'dcf_check__identity_verification__2',
    'label' => 'Identities verified',
    'description' => 'Verify all identities have been successfully verified.',
    'section' => 'Identity Verification',
    'stage_key' => 'dcf_check',
    'order' => 2,
  ),
  110 => 
  array (
    'key' => 'dcf_check__identity_verification__3',
    'label' => 'ID documents valid',
    'description' => 'Confirm identification documents are valid.',
    'section' => 'Identity Verification',
    'stage_key' => 'dcf_check',
    'order' => 3,
  ),
  111 => 
  array (
    'key' => 'dcf_check__banking_verification__1',
    'label' => 'Bank signatories confirmed',
    'description' => 'Confirm all authorised bank signatories.',
    'section' => 'Banking Verification',
    'stage_key' => 'dcf_check',
    'order' => 4,
  ),
  112 => 
  array (
    'key' => 'dcf_check__banking_verification__2',
    'label' => 'Account belongs to applicant',
    'description' => 'Verify the bank account belongs to the applicant organisation.',
    'section' => 'Banking Verification',
    'stage_key' => 'dcf_check',
    'order' => 5,
  ),
  113 => 
  array (
    'key' => 'dcf_check__banking_verification__3',
    'label' => 'Banking details independently verified',
    'description' => 'Confirm banking details have been independently verified.',
    'section' => 'Banking Verification',
    'stage_key' => 'dcf_check',
    'order' => 6,
  ),
  114 => 
  array (
    'key' => 'dcf_check__financial_integrity__1',
    'label' => 'AML checks completed',
    'description' => 'Confirm Anti-Money Laundering (AML) checks have been completed.',
    'section' => 'Financial Integrity',
    'stage_key' => 'dcf_check',
    'order' => 7,
  ),
  115 => 
  array (
    'key' => 'dcf_check__financial_integrity__2',
    'label' => 'PEP checks completed',
    'description' => 'Confirm Politically Exposed Person (PEP) checks have been completed.',
    'section' => 'Financial Integrity',
    'stage_key' => 'dcf_check',
    'order' => 8,
  ),
  116 => 
  array (
    'key' => 'dcf_check__financial_integrity__3',
    'label' => 'Sanctions checks completed',
    'description' => 'Confirm international sanctions checks have been completed.',
    'section' => 'Financial Integrity',
    'stage_key' => 'dcf_check',
    'order' => 9,
  ),
  117 => 
  array (
    'key' => 'dcf_check__fraud_prevention__1',
    'label' => 'No fraud indicators',
    'description' => 'Check for any indicators of fraudulent activity.',
    'section' => 'Fraud Prevention',
    'stage_key' => 'dcf_check',
    'order' => 10,
  ),
  118 => 
  array (
    'key' => 'dcf_check__fraud_prevention__2',
    'label' => 'Conflicts of interest identified',
    'description' => 'Determine whether any conflicts of interest have been identified.',
    'section' => 'Fraud Prevention',
    'stage_key' => 'dcf_check',
    'order' => 11,
  ),
  119 => 
  array (
    'key' => 'dcf_check__fraud_prevention__3',
    'label' => 'Safeguarding concerns',
    'description' => 'Check for any safeguarding concerns.',
    'section' => 'Fraud Prevention',
    'stage_key' => 'dcf_check',
    'order' => 12,
  ),
  120 => 
  array (
    'key' => 'dcf_check__financial_recommendation__1',
    'label' => 'Safe to proceed to Finance',
    'description' => 'Confirm the application can safely proceed to Finance.',
    'section' => 'Financial Recommendation',
    'stage_key' => 'dcf_check',
    'order' => 13,
  ),
  121 => 
  array (
    'key' => 'dcf_check__financial_recommendation__2',
    'label' => 'Additional checks required',
    'description' => 'Determine whether additional financial checks are required.',
    'section' => 'Financial Recommendation',
    'stage_key' => 'dcf_check',
    'order' => 14,
  ),
  122 => 
  array (
    'key' => 'dcf_check__financial_recommendation__3',
    'label' => 'Passed all safeguarding requirements',
    'description' => 'Confirm the application has passed all safeguarding requirements.',
    'section' => 'Financial Recommendation',
    'stage_key' => 'dcf_check',
    'order' => 15,
  ),
  123 => 
  array (
    'key' => 'payout_processing__funding_approval__1',
    'label' => 'All approvals received',
    'description' => 'Confirm the application has received all required approvals.',
    'section' => 'Funding Approval',
    'stage_key' => 'payout_processing',
    'order' => 1,
  ),
  124 => 
  array (
    'key' => 'payout_processing__funding_approval__2',
    'label' => 'Funding agreement executed',
    'description' => 'Verify the funding agreement has been fully executed.',
    'section' => 'Funding Approval',
    'stage_key' => 'payout_processing',
    'order' => 2,
  ),
  125 => 
  array (
    'key' => 'payout_processing__funding_approval__3',
    'label' => 'Approved amount recorded',
    'description' => 'Confirm the approved funding amount is correctly recorded.',
    'section' => 'Funding Approval',
    'stage_key' => 'payout_processing',
    'order' => 3,
  ),
  126 => 
  array (
    'key' => 'payout_processing__payment_verification__1',
    'label' => 'Payment details verified',
    'description' => 'Verify the payment details.',
    'section' => 'Payment Verification',
    'stage_key' => 'payout_processing',
    'order' => 4,
  ),
  127 => 
  array (
    'key' => 'payout_processing__payment_verification__2',
    'label' => 'Payment schedule correct',
    'description' => 'Confirm the payment schedule is correct.',
    'section' => 'Payment Verification',
    'stage_key' => 'payout_processing',
    'order' => 5,
  ),
  128 => 
  array (
    'key' => 'payout_processing__payment_verification__3',
    'label' => 'Payment authorised',
    'description' => 'Confirm the payment has been authorised.',
    'section' => 'Payment Verification',
    'stage_key' => 'payout_processing',
    'order' => 6,
  ),
  129 => 
  array (
    'key' => 'payout_processing__financial_records__1',
    'label' => 'Payment recorded in finance system',
    'description' => 'Confirm the payment has been recorded within the finance system.',
    'section' => 'Financial Records',
    'stage_key' => 'payout_processing',
    'order' => 7,
  ),
  130 => 
  array (
    'key' => 'payout_processing__financial_records__2',
    'label' => 'Transaction reference generated',
    'description' => 'Verify a transaction reference has been generated.',
    'section' => 'Financial Records',
    'stage_key' => 'payout_processing',
    'order' => 8,
  ),
  131 => 
  array (
    'key' => 'payout_processing__financial_records__3',
    'label' => 'Financial records updated',
    'description' => 'Confirm financial records have been updated.',
    'section' => 'Financial Records',
    'stage_key' => 'payout_processing',
    'order' => 9,
  ),
  132 => 
  array (
    'key' => 'payout_processing__stakeholder_communication__1',
    'label' => 'Donor notification prepared',
    'description' => 'Confirm the donor notification has been prepared.',
    'section' => 'Stakeholder Communication',
    'stage_key' => 'payout_processing',
    'order' => 10,
  ),
  133 => 
  array (
    'key' => 'payout_processing__stakeholder_communication__2',
    'label' => 'Board notification prepared',
    'description' => 'Confirm the Directors / Trustees notification has been prepared.',
    'section' => 'Stakeholder Communication',
    'stage_key' => 'payout_processing',
    'order' => 11,
  ),
  134 => 
  array (
    'key' => 'payout_processing__stakeholder_communication__3',
    'label' => 'Payment memos generated',
    'description' => 'Confirm all required payment memos have been generated.',
    'section' => 'Stakeholder Communication',
    'stage_key' => 'payout_processing',
    'order' => 12,
  ),
  135 => 
  array (
    'key' => 'payout_processing__monitoring_for_future_payments__1',
    'label' => 'PMCU monitoring report submitted',
    'description' => 'Confirm the PMCU has submitted the required monitoring report.',
    'section' => 'Monitoring for Future Payments',
    'stage_key' => 'payout_processing',
    'order' => 13,
  ),
  136 => 
  array (
    'key' => 'payout_processing__monitoring_for_future_payments__2',
    'label' => 'Project milestones achieved',
    'description' => 'Verify project milestones have been achieved.',
    'section' => 'Monitoring for Future Payments',
    'stage_key' => 'payout_processing',
    'order' => 14,
  ),
  137 => 
  array (
    'key' => 'payout_processing__monitoring_for_future_payments__3',
    'label' => 'Evidence supports next payment',
    'description' => 'Confirm there is sufficient evidence to support release of the next payment.',
    'section' => 'Monitoring for Future Payments',
    'stage_key' => 'payout_processing',
    'order' => 15,
  ),
  138 => 
  array (
    'key' => 'payout_processing__monitoring_for_future_payments__4',
    'label' => 'Project expenses reviewed & accepted',
    'description' => 'Confirm project expenses have been reviewed and accepted.',
    'section' => 'Monitoring for Future Payments',
    'stage_key' => 'payout_processing',
    'order' => 16,
  ),
);

        foreach ($rows as $row) {
            Sector::updateOrCreate(['key' => $row['key']], $row);
        }
    }
}
