<?php

namespace Modules\Clinic\Http\Controllers;

use App\Contact;
use App\Division;
use App\Transaction;
use App\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Utils\ModuleUtil;
use App\Utils\Util;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Log;
use Modules\Clinic\Entities\PatientAppointmentRequ;
use Modules\Clinic\Utils\AppointmentUtil;
use Modules\Clinic\Utils\PrescriptionUtil;
use App\Utils\ContactUtil;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Modules\Clinic\Entities\DoctorAdvice;
use Modules\Clinic\Entities\Dosage;
use Modules\Clinic\Entities\PrescribeTest;
use Modules\Clinic\Entities\Duration;
use Modules\Clinic\Entities\MedicineMeal;
use Modules\Clinic\Entities\ChiefComplain;
use Modules\Clinic\Entities\PatientProfile;
use Modules\Clinic\Entities\PrescribeMedicine;
use Modules\Clinic\Entities\Prescription;
use Modules\Clinic\Entities\PrescriptionTemplate;
use Modules\Clinic\Entities\PrescribeTherapie;
use Modules\Clinic\Entities\PrescribeAdvice;
use Modules\Clinic\Entities\DoctorProfile;
use Modules\Clinic\Entities\PrescribeIpdAdmission;
use Modules\Clinic\Entities\PrescribedCC;
use Modules\Clinic\Entities\Frequency;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Modules\Clinic\Entities\DiseaseHistory;
use Modules\Clinic\Entities\DoctorSL;
use Modules\Clinic\Entities\InvestigationHistory;
use Modules\Clinic\Entities\MissingMedicine;
use Modules\Clinic\Entities\MissingTest;
use Modules\Clinic\Entities\MissingTherapy;
use Modules\Clinic\Entities\PatientDisease;

class PrescriptionsController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    protected $appointmentUtil;
    protected $contactUtil;
    protected $commonUtil;
    protected $prescriptionUtil;
    public function __construct(AppointmentUtil $appointmentUtil, ContactUtil $contactUtil, Util $commonUtil, PrescriptionUtil $prescriptionUtil)
    {
        $this->appointmentUtil = $appointmentUtil;
        $this->contactUtil = $contactUtil;
        $this->commonUtil = $commonUtil;
        $this->prescriptionUtil = $prescriptionUtil;
    }


    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = $this->prescriptionUtil->getPrescriptionQuery();
            $prescription = Datatables::of($query)
                ->addColumn('assigned_doctor', function ($row) {
                    return $row->assigned_doctor_first_name . ' ' . $row->assigned_doctor_last_name ?? '';
                })
                ->addColumn('patient_name', function ($row) {
                    return $row->patient_first_name . ' ' . $row->patient_last_name ?? '';
                })
                ->rawColumns(['assigned_doctor', 'patient_name'])
                ->make(true);
            return $prescription;
            $query = $this->prescriptionUtil->getPrescriptionQuery();
            $prescription = Datatables::of($query)
                ->addColumn('assigned_doctor', function ($row) {
                    return $row->assigned_doctor_first_name . ' ' . $row->assigned_doctor_last_name ?? '';
                })
                ->addColumn('patient_name', function ($row) {
                    return $row->patient_first_name . ' ' . $row->patient_last_name ?? '';
                })
                ->rawColumns(['assigned_doctor', 'patient_name'])
                ->make(true);
            return $prescription;
        }
        return view('clinic::prescriptions.index');
    }




    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        // $advices = DoctorAdvice::all();
        // $dosages =Dosage::all();

        // return view('clinic::prescriptions.create', compact('advices', 'dosages'));
    }


    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'appointment_id' => 'required',
                'visit_date' => 'date',
                'current_height' => 'nullable',
                'current_height_feet' => 'nullable',
                'current_height_inches' => 'nullable',
                'current_weight' => 'nullable',
                'blood_pressure' => 'nullable',
                'diastolic_pressure' => 'nullable',
                'systolic_pressure' => 'nullable',
                'respiratory' => 'nullable',
                'body_temp' => 'nullable',
                'bmi' => 'nullable',
                'body_fat_percent' => 'nullable',
                'fat_mass_percent' => 'nullable',
                'lean_mass_percent' => 'nullable',
                'taken_instruction' => 'required|array',
                'dosage_form' => 'required|array',
                'medicine_name' => 'required|array',
                'generic_name' => 'nullable|array',
                'disease_history' => 'nullable|array',
                'investigation_history' => 'nullable|array',
                'product_id' => 'nullable',
                'generic_id' => 'nullable',
                'follow_up' => 'nullable',
                'follow_up_number' => 'nullable',
                'follow_up_type' => 'nullable',
                'test_name' => 'nullable|array',
                'test_comment' => 'nullable|array',
                'therapy_frequency' => 'nullable|array',
                'medicine_comment' => 'nullable|array',
                'complain_name' => 'nullable|array',
                'complain_comment' => 'nullable|array',
                'test_product_id' => 'nullable',
                'advice_id' => 'nullable|array',
                'note' => 'nullable',
                'comments' => 'nullable',
                'action' => 'nullable',
                'pulse_rate' => 'nullable',
                'template_name' => 'nullable',
                'start_time' => 'nullable',
                'therapy_name' => 'nullable',
                'therapy_product_id' => 'nullable|array',
                'chief_complain_id' => 'nullable|array',
                'medication_duration' => 'required|array',
                'session_count' => 'nullable|array',
                'age' => 'nullable',
                'gender' => 'nullable',
                'template_id' => 'nullable|exists:prescriptions,id',
                'ipd_admission' => 'nullable|boolean',
                'ipd_days' => 'nullable|integer|required_if:ipd_admission,1|min:1',
            ]);

            $response = $this->prescriptionUtil->generatePrescription($data);

            $patient = PatientProfile::where('patient_contact_id', $response['prescription']->patient_contact_id)->first();

            $patient->age = $data['age'];
            $patient->gender = $data['gender'];
            $patient->save();

            if ($response['success'] == true) {
                if ($data['action'] == 'save_and_print') {
                    $pid = $response['prescription']['id'];
                    return redirect()
                        ->action([\Modules\Clinic\Http\Controllers\PrescriptionsController::class, 'printView'], ['id' => $pid]);
                }
                if ($data['action'] == 'save_as_template') {
                    return redirect()->back();
                }
                $output = [
                    'success' => true,
                    'msg' => 'Prescription Added Successfully',
                ];
            } else if ($response['success'] == false) {
                $output = [
                    'success' => false,
                    'msg' => $response['msg'],
                ];
            }
        } catch (\Exception $e) {
            Log::error('Error in storing prescription: ' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => $e->getMessage(),
            ];
        }
        if ($output['success'] == true) {
            return redirect()->route('doctor-dashboard.index')->with('status', $output);
        } elseif ($output['success'] == false) {
            return redirect()->back()->with('status', $output);
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $temp = request()->get('is_template');

        $prescription = DB::table('prescriptions')
            ->join('patient_profiles', 'patient_profiles.id', '=', 'prescriptions.patient_profile_id')
            ->join('users as created_by_user', 'created_by_user.id', '=', 'prescriptions.created_by')
            ->leftJoin('contacts', 'patient_profiles.patient_contact_id', '=', 'contacts.id')
            ->leftJoin('prescribed_medicines', 'prescribed_medicines.prescription_id', '=', 'prescriptions.id')
            ->leftJoin('prescription_advises', 'prescription_advises.prescription_id', '=', 'prescriptions.id')
            ->leftJoin('dosage', 'dosage.id', '=', 'prescribed_medicines.taken_instruction')
            ->leftJoin('medicine_meal', 'medicine_meal.id', '=', 'prescribed_medicines.dosage_form')
            ->leftJoin('durations', 'durations.id', '=', 'prescribed_medicines.medication_duration')
            ->leftJoin('prescribe_tests', 'prescribe_tests.prescription_id', '=', 'prescriptions.id')
            ->leftJoin('prescription_therapies', 'prescription_therapies.prescription_id', '=', 'prescriptions.id')
            ->leftJoin('prescribed_cc', 'prescribed_cc.prescription_id', '=', 'prescriptions.id')
            ->leftJoin('patient_appointment_requests', 'patient_appointment_requests.patient_profile_id', '=', 'prescriptions.patient_profile_id')
            ->leftJoin('users as assigned_doctor', 'assigned_doctor.id', '=', 'prescriptions.doctor_user_id')
            ->leftJoin('doctor_profiles', 'doctor_profiles.user_id', '=', 'assigned_doctor.id')
            ->leftJoin('doctor_degrees', 'doctor_profiles.id', '=', 'doctor_degrees.doctor_profile_id')
            ->leftJoin('doctor_specialities', 'doctor_profiles.id', '=', 'doctor_specialities.doctor_profile_id')
            ->select(
                'prescriptions.*',
                'contacts.contact_id as customerId',
                'patient_appointment_requests.doctor_user_id as assigned_doctor_id',
                'patient_appointment_requests.doctor_profile_id as assigned_doc_id',
                'patient_profiles.first_name as patient_first_name',
                'patient_profiles.last_name as patient_last_name',
                'patient_profiles.mobile as patient_mobile',
                'patient_profiles.age as age',
                'patient_profiles.gender as gender',
                'created_by_user.first_name as creator_first_name',
                'created_by_user.last_name as creator_last_name',
                'assigned_doctor.first_name as assigned_doctor_first_name',
                'assigned_doctor.last_name as assigned_doctor_last_name',
                'doctor_profiles.show_in_pad',
                DB::raw('GROUP_CONCAT(DISTINCT prescribed_medicines.x_medicine_name ORDER BY prescribed_medicines.x_medicine_name ASC) as medicines'),
                DB::raw('GROUP_CONCAT(DISTINCT prescribe_tests.comment) as tests'),
                DB::raw('GROUP_CONCAT(DISTINCT prescription_advises.advise_name ORDER BY prescription_advises.advise_name ASC) as advices'),
                DB::raw('GROUP_CONCAT(DISTINCT prescription_therapies.therapy_name ORDER BY prescription_therapies.therapy_name ASC) as therapies'),
                DB::raw('GROUP_CONCAT(DISTINCT prescribed_cc.comment) as complains'),
                DB::raw('GROUP_CONCAT(DISTINCT CONCAT(doctor_degrees.degree_name, " (", doctor_degrees.degree_short_name, ")") ORDER BY doctor_degrees.id ASC) as doctor_degrees'),
                DB::raw('GROUP_CONCAT(DISTINCT CONCAT(doctor_specialities.term_name	, " (", doctor_specialities.term_short_name, ")") ORDER BY doctor_specialities.id ASC) as doctor_specialities'),
                DB::raw('GROUP_CONCAT(dosage.value) as dosage_instructions'),
                DB::raw('GROUP_CONCAT(medicine_meal.value) as dosage_forms'),
                DB::raw('GROUP_CONCAT(durations.value) as medication_durations')
            )
            ->where('prescriptions.id', $id)
            ->groupBy(
                'prescriptions.id',
                'patient_profiles.first_name',
                'patient_profiles.last_name',
                'patient_profiles.mobile',
                'created_by_user.first_name',
                'created_by_user.last_name',
                'assigned_doctor.first_name',
                'assigned_doctor.last_name'
            )
            ->first();




        $prescribedMedicines = PrescribeMedicine::where('prescription_id', $prescription->id)
            ->leftJoin('dosage', 'dosage.id', '=', 'prescribed_medicines.taken_instruction')
            ->leftJoin('medicine_meal', 'medicine_meal.id', '=', 'prescribed_medicines.dosage_form')
            ->leftJoin('durations', 'durations.id', '=', 'prescribed_medicines.medication_duration')
            ->select(
                'prescribed_medicines.*',
                'dosage.value as dosage',
                'medicine_meal.value as medicine_meal',
                'durations.value as medication_duration'
            )
            ->get();
        $prescribedTest = PrescribeTest::where('prescription_id', $prescription->id)->get();
        $prescribedTherapy = PrescribeTherapie::where('prescription_id', $prescription->id)
            ->leftJoin('frequencies', 'prescription_therapies.frequency', '=', 'frequencies.id')
            ->select(
                'prescription_therapies.*',
                'frequencies.value as frequency'
            )
            ->get();

        $diseaseHistories = DiseaseHistory::where('prescription_id', $prescription->id)
            ->leftJoin('chief_complaint', 'chief_complaint.id', '=', 'disease_history.chief_complaint_id')
            ->select(
                'disease_history.*',
                'chief_complaint.value as chief_complaint'
            )
            ->get();
        $InvestigationHistory = InvestigationHistory::where('prescription_id', $prescription->id)->get();
        $ipdAdmission = PrescribeIpdAdmission::where('prescription_id', $prescription->id)->first();

        if (!$prescription) {
            return back()->with('error', 'Prescription not found!');
        }
        if ($temp == 1) {
            return view('clinic::prescriptions.template.view', compact('prescription', 'prescribedMedicines', 'prescribedTest', 'prescribedTherapy', 'diseaseHistories', 'InvestigationHistory', 'ipdAdmission'));
        }

        $common_settings = session()->get('business.common_settings');

        return view('clinic::prescriptions.show', compact('prescription', 'prescribedMedicines', 'prescribedTest', 'prescribedTherapy', 'diseaseHistories', 'InvestigationHistory', 'common_settings', 'ipdAdmission'));
    }
    public function printView($id)
    {
        $prescription = DB::table('prescriptions')
            ->join('patient_profiles', 'patient_profiles.id', '=', 'prescriptions.patient_profile_id')
            ->join('users as created_by_user', 'created_by_user.id', '=', 'prescriptions.created_by')
            ->leftJoin('contacts', 'patient_profiles.patient_contact_id', '=', 'contacts.id')
            ->leftJoin('prescribed_medicines', 'prescribed_medicines.prescription_id', '=', 'prescriptions.id')
            ->leftJoin('prescription_advises', 'prescription_advises.prescription_id', '=', 'prescriptions.id')
            ->leftJoin('dosage', 'dosage.id', '=', 'prescribed_medicines.taken_instruction')
            ->leftJoin('medicine_meal', 'medicine_meal.id', '=', 'prescribed_medicines.dosage_form')
            ->leftJoin('durations', 'durations.id', '=', 'prescribed_medicines.medication_duration')
            ->leftJoin('prescribe_tests', 'prescribe_tests.prescription_id', '=', 'prescriptions.id')
            ->leftJoin('prescription_therapies', 'prescription_therapies.prescription_id', '=', 'prescriptions.id')
            ->leftJoin('prescribed_cc', 'prescribed_cc.prescription_id', '=', 'prescriptions.id')
            ->leftJoin('patient_appointment_requests', 'patient_appointment_requests.patient_profile_id', '=', 'prescriptions.patient_profile_id')
            ->leftJoin('users as assigned_doctor', 'assigned_doctor.id', '=', 'prescriptions.doctor_user_id')
            ->leftJoin('doctor_profiles', 'doctor_profiles.user_id', '=', 'assigned_doctor.id')
            ->leftJoin('doctor_degrees', 'doctor_profiles.id', '=', 'doctor_degrees.doctor_profile_id')
            ->leftJoin('doctor_specialities', 'doctor_profiles.id', '=', 'doctor_specialities.doctor_profile_id')
            ->select(
                'prescriptions.*',
                'contacts.contact_id as customerId',
                'patient_appointment_requests.doctor_user_id as assigned_doctor_id',
                'patient_appointment_requests.doctor_profile_id as assigned_doc_id',
                'patient_profiles.first_name as patient_first_name',
                'patient_profiles.last_name as patient_last_name',
                'patient_profiles.mobile as patient_mobile',
                'patient_profiles.age as age',
                'patient_profiles.gender as gender',
                'created_by_user.first_name as creator_first_name',
                'created_by_user.last_name as creator_last_name',
                'assigned_doctor.first_name as assigned_doctor_first_name',
                'assigned_doctor.last_name as assigned_doctor_last_name',
                'doctor_profiles.show_in_pad',
                DB::raw('GROUP_CONCAT(DISTINCT prescribed_medicines.x_medicine_name) as medicines'),

                DB::raw('GROUP_CONCAT(DISTINCT prescribe_tests.comment) as tests'),
                DB::raw('GROUP_CONCAT(DISTINCT prescription_advises.advise_name) as advices'),
                DB::raw('GROUP_CONCAT(DISTINCT prescription_therapies.therapy_name) as therapies'),
                DB::raw('GROUP_CONCAT(DISTINCT prescribed_cc.comment) as complains'),
                DB::raw('GROUP_CONCAT(DISTINCT CONCAT(doctor_specialities.term_name	, " (", doctor_specialities.term_short_name, ")")) as doctor_specialities'),
                DB::raw('GROUP_CONCAT(dosage.value) as dosage_instructions'),
                DB::raw('GROUP_CONCAT(medicine_meal.value) as dosage_forms'),
                DB::raw('GROUP_CONCAT(durations.value) as medication_durations'),
                DB::raw('GROUP_CONCAT(
                    DISTINCT 
                    CONCAT(
                        IF(JSON_CONTAINS(doctor_degrees.show_in_pad, \'"degree_name"\'), doctor_degrees.degree_name, ""),
                        IF(
                            JSON_CONTAINS(doctor_degrees.show_in_pad, \'"degree_short_name"\') OR 
                            JSON_CONTAINS(doctor_degrees.show_in_pad, \'"certification_place"\'),
                            CONCAT(
                                " (",
                                IF(JSON_CONTAINS(doctor_degrees.show_in_pad, \'"degree_short_name"\'), doctor_degrees.degree_short_name, ""),
                                IF(
                                    JSON_CONTAINS(doctor_degrees.show_in_pad, \'"degree_short_name"\') 
                                    AND JSON_CONTAINS(doctor_degrees.show_in_pad, \'"certification_place"\'),
                                    ", ",
                                    ""
                                ),
                                IF(JSON_CONTAINS(doctor_degrees.show_in_pad, \'"certification_place"\'), doctor_degrees.certification_place, ""),
                                ")"
                            ),
                            ""
                        ),
                        "<br>"
                    )
                    ORDER BY doctor_degrees.id
                    SEPARATOR ""
                ) as doctor_degrees')
            )
            ->where('prescriptions.id', $id)
            ->groupBy(
                'prescriptions.id',
                'patient_profiles.first_name',
                'patient_profiles.last_name',
                'patient_profiles.mobile',
                'created_by_user.first_name',
                'created_by_user.last_name',
                'assigned_doctor.first_name',
                'assigned_doctor.last_name'
            )
            ->first();

        Log::info(['doctor_degree' => $prescription->doctor_degrees]);

        if (!$prescription) {
            return back()->with('error', 'Prescription not found!');
        }

        $prescribedMedicines = PrescribeMedicine::where('prescription_id', $prescription->id)
            ->leftJoin('dosage', 'dosage.id', '=', 'prescribed_medicines.taken_instruction')
            ->leftJoin('medicine_meal', 'medicine_meal.id', '=', 'prescribed_medicines.dosage_form')
            ->leftJoin('durations', 'durations.id', '=', 'prescribed_medicines.medication_duration')
            ->select(
                'prescribed_medicines.*',
                'dosage.value as dosage',
                'medicine_meal.value as medicine_meal',
                'durations.value as medication_duration'
            )
            ->get();
        $prescribedTest = PrescribeTest::where('prescription_id', $prescription->id)->get();
        $prescribedTherapy = PrescribeTherapie::where('prescription_id', $prescription->id)
            ->leftJoin('frequencies', 'prescription_therapies.frequency', '=', 'frequencies.id')
            ->select(
                'prescription_therapies.*',
                'frequencies.value as frequency'
            )
            ->get();

        $diseaseHistories = DiseaseHistory::where('prescription_id', $prescription->id)
            ->leftJoin('chief_complaint', 'chief_complaint.id', '=', 'disease_history.chief_complaint_id')
            ->select(
                'disease_history.*',
                'chief_complaint.value as chief_complaint'
            )
            ->get();
        $InvestigationHistory = InvestigationHistory::where('prescription_id', $prescription->id)->get();

        $ipdAdmission = PrescribeIpdAdmission::where('prescription_id', $prescription->id)->first();

        $common_settings = session()->get('business.common_settings');

        return view('clinic::prescriptions.direct_print', compact('prescription', 'prescribedMedicines', 'prescribedTest', 'prescribedTherapy', 'diseaseHistories', 'InvestigationHistory', 'ipdAdmission', 'common_settings'));
    }




    public function showInDoctor($id)
    {
        $advices = DoctorAdvice::where('status', 1)->get();
        $dosages = Dosage::pluck('value', 'id');
        $appointment = PatientAppointmentRequ::find($id);
        $patient = PatientProfile::where('patient_contact_id', $appointment->patient_contact_id)->first();
        $doctor = DoctorProfile::where('id', $appointment->doctor_profile_id)->first();
        $doctor_first_name = $doctor->first_name;
        $doctor_last_name = $doctor->last_name ?? '';
        $doctor_name = $doctor_first_name . ' ' . $doctor_last_name;
        if (!$patient) {
            return redirect()->back()->withErrors(['error' => 'Patient not found']);
        }
        $meals = MedicineMeal::where('status', 1)->pluck('value', 'id');
        $durations = Duration::where('status', 1)->pluck('value', 'id');
        $complains = ChiefComplain::where('status', 1)->pluck('value', 'id');
        $frequencies = Frequency::where('status', 1)->pluck('value', 'id');
        $templates = PrescriptionTemplate::join('users', 'prescription_templates.created_by', '=', 'users.id')
            ->join('prescriptions', 'prescription_templates.prescription_id', '=', 'prescriptions.id')
            ->join('users as doctors', 'prescriptions.doctor_user_id', '=', 'doctors.id')
            ->select('prescription_templates.name', 'prescription_templates.prescription_id', 'users.username', 'doctors.first_name')
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->prescription_id => $item->name . ' (' . $item->first_name . ')'
                ];
            })
            ->toArray();


        $prescription = Prescription::where('appointment_id', $id)->first();
        $prescribedMedicines = null;
        $prescribedTest = null;
        $prescribedTherapy = null;
        $prescribedComplain = null;
        $existingComplainIds = [];
        $diseaseHistories = collect();
        $InvestigationHistory = collect();
        $otherPrescription = [];
        $visitCount = null;
        $visitNo = 1;
        if ($prescription) {
            $prescribedMedicines = PrescribeMedicine::where('prescription_id', $prescription->id)->get();
            $prescribedComplain = PrescribedCC::where('prescription_id', $prescription->id)->get();
            $prescribedTest = PrescribeTest::where('prescription_id', $prescription->id)->get();
            $prescribedTherapy = PrescribeTherapie::where('prescription_id', $prescription->id)->get();
            $existingAdvice = PrescribeAdvice::where('prescription_id', $prescription->id)->get();
            $existingComplainIds = PrescribedCC::where('prescription_id', $prescription->id)
                ->pluck('complain_id')->toArray();
            $diseaseHistories = DiseaseHistory::where('prescription_id', $prescription->id)
                ->pluck('chief_complaint_id');
            $InvestigationHistory = InvestigationHistory::where('prescription_id', $prescription->id)->get();

            $ipdAdmission = PrescribeIpdAdmission::where('prescription_id', $prescription->id)->first();

        } else {
            // Handle the case where $prescription is null
            $diseaseHistories = collect();
            $InvestigationHistory = collect();
            $existingAdvice = collect();
            $ipdAdmission = null;
        }

        $prescriptions = Prescription::where('patient_contact_id', $appointment->patient_contact_id)
            ->orderBy('prescription_date', 'asc')
            ->get(['prescription_date', 'appointment_id', 'id']); // Fetch prescription_id
        $hasOldPrescriptions = $prescriptions->isNotEmpty();


        if ($hasOldPrescriptions) {
            foreach ($prescriptions as $presc) {
                $formattedDate = \Carbon\Carbon::parse($presc->prescription_date)->format('d-m-Y'); // Format date
                $checkApp = PatientAppointmentRequ::where('id', $presc->appointment_id)->first();
                $dr = DoctorProfile::where('user_id', $checkApp->doctor_user_id)->first();
                $otherPrescription[$presc->appointment_id] = [
                    'label' => "Visit-$visitNo / $formattedDate /" .
                        (($dr->first_name ?? '') . ' ' . ($dr->last_name ?? '')),
                    'prescription_id' => $presc->id // Store prescription ID
                ];
                $visitNo++;
            }
        }
        $visitCount = count($otherPrescription);
        $now = now();
        $ChiefComplain = ChiefComplain::where('status', 1)->pluck('value', 'id');


        $sl = DoctorSL::where('appointment_id', $id)->first();
        if ($sl) {
            $sl->status = 'served';
            $sl->save();
        }

        $common_settings = session()->get('business.common_settings');

        return view('clinic::prescriptions.create_in_doctor', compact(
            'patient',
            'advices',
            'dosages',
            'meals',
            'durations',
            'complains',
            'templates',
            'prescription',
            'prescribedMedicines',
            'prescribedTest',
            'prescribedTherapy',
            'existingAdvice',
            'existingComplainIds',
            'prescribedComplain',
            'otherPrescription',
            'visitCount',
            'appointment',
            'now',
            'frequencies',
            'doctor_name',
            'ChiefComplain',
            'diseaseHistories',
            'InvestigationHistory',
            'common_settings',
            'ipdAdmission'
        ));
    }


    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('clinic::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }

    public function storeTemplate(Request $request)
    {
        $request->validate([
            'template_name_hidden' => 'required|unique:prescription_templates,name',
            'appointment_id' => 'required'
        ], [
            'template_name_hidden.unique' => 'This template name is already taken',
        ]);

        try {
            $template = new PrescriptionTemplate();
            $template->name = $request->input('template_name_hidden');
            $template->appointment_id = $request->input('appointment_id');
            $template->save();
            $output = [
                'success' => true,
                'msg' => 'Template save successfully'
            ];
        } catch (\Exception $e) {
            Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => false,
                'msg' => 'Something went wrong'
            ];
        }

        return $output;
    }
    public function templateExistOrNot($id)
    {
        $temp = PrescriptionTemplate::where('appointment_id', $id)->first();
        if ($temp) {
            $output = [
                'success' => true,
            ];
        } else {
            $output = [
                'success' => false,
            ];
        }
        return $output;
    }

    public function getTemplateData(Request $request, $appointment, $prescription)
    {
        $height = $request->input('height');
        $current_height_feet = $request->input('current_height_feet');
        $current_height_inches = $request->input('current_height_inches');
        $weight = $request->input('weight');
        $pulse = $request->input('pulse');
        $bp = $request->input('bp');
        $systolic_pressure = $request->input('systolic_pressure');
        $diastolic_pressure = $request->input('diastolic_pressure');
        $respiratory = $request->input('respiratory');
        $bodyTemp = $request->input('body_temp');
        $bmi = $request->input('bmi');
        $body_fat_percent = $request->input('body_fat_percent');
        $fatMassPercent = $request->input('fat_mass_percent');
        $leanMassPercent = $request->input('lean_mass_percent');
        $data = [
            'current_height_feet' => $current_height_feet,
            'current_height_inches' => $current_height_inches,
            'current_height' => $height,
            'current_weight' => $weight,
            'pulse_rate' => $pulse,
            'blood_pressure' => $bp,
            'systolic_pressure' => $systolic_pressure,
            'diastolic_pressure' => $diastolic_pressure,
            'respiratory' => $respiratory,
            'body_temp' => $bodyTemp,
            'bmi' => $bmi,
            'body_fat_percent' => $body_fat_percent,
            'fat_mass_percent' => $fatMassPercent,
            'lean_mass_percent' => $leanMassPercent,
        ];
        $prescription = Prescription::where('id', $prescription)->first();
        $appointment = PatientAppointmentRequ::where('id', $appointment)->first();
        $patient = PatientProfile::where('patient_contact_id', $appointment->patient_contact_id)->first();

        $advices = DoctorAdvice::where('status', 1)->get();
        $dosages = Dosage::pluck('value', 'id');

        $meals = MedicineMeal::where('status', 1)->pluck('value', 'id');
        $durations = Duration::where('status', 1)->pluck('value', 'id');
        $complains = ChiefComplain::where('status', 1)->pluck('value', 'id');
        $frequencies = Frequency::where('status', 1)->pluck('value', 'id');

        $templateId = $prescription->id;

        $templates = PrescriptionTemplate::join('users', 'prescription_templates.created_by', '=', 'users.id')
            ->join('prescriptions', 'prescription_templates.prescription_id', '=', 'prescriptions.id')
            ->join('users as doctors', 'prescriptions.doctor_user_id', '=', 'doctors.id')
            ->select('prescription_templates.name', 'prescription_templates.prescription_id', 'users.username', 'doctors.first_name')
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->prescription_id => $item->name . ' (' . $item->first_name . ')'
                ];
            })
            ->toArray();
        $prescribedMedicines = null;
        $prescribedTest = null;
        $prescribedTherapy = null;
        $prescribedComplain = null;
        $existingComplainIds = [];
        $diseaseHistories = collect();
        $InvestigationHistory = collect();
        $otherPrescription = [];
        $visitNo = 1;
        if ($prescription) {
            $prescribedMedicines = PrescribeMedicine::where('prescription_id', $prescription->id)->get();
            $prescribedComplain = PrescribedCC::where('prescription_id', $prescription->id)->get();
            $prescribedTest = PrescribeTest::where('prescription_id', $prescription->id)->get();
            $prescribedTherapy = PrescribeTherapie::where('prescription_id', $prescription->id)->get();
            $existingAdvice = PrescribeAdvice::where('prescription_id', $prescription->id)->get();
            $existingComplainIds = PrescribedCC::where('prescription_id', $prescription->id)
                ->pluck('complain_id')->toArray();
            $diseaseHistories = DiseaseHistory::where('prescription_id', $prescription->id)
                ->pluck('chief_complaint_id');
            $InvestigationHistory = InvestigationHistory::where('prescription_id', $prescription->id)->get();
        } else {
            // Handle the case where $prescription is null
            $diseaseHistories = collect();
            $InvestigationHistory = collect();
            $existingAdvice = collect(); // Return an empty collection or handle as needed
        }

        $prescriptions = Prescription::where('patient_contact_id', $appointment->patient_contact_id)
            ->orderBy('prescription_date', 'asc')
            ->get(['prescription_date', 'appointment_id', 'id']); // Fetch prescription_id

        $hasOldPrescriptions = $prescriptions->isNotEmpty();

        if ($hasOldPrescriptions) {
            foreach ($prescriptions as $presc) {
                $formattedDate = \Carbon\Carbon::parse($presc->prescription_date)->format('d-m-Y'); // Format date
                $checkApp = PatientAppointmentRequ::where('id', $presc->appointment_id)->first();
                $dr = DoctorProfile::where('user_id', $checkApp->doctor_user_id)->first();
                $otherPrescription[$presc->appointment_id] = [
                    'label' => "Visit-$visitNo / $formattedDate /" . $dr->first_name . ' ' . $dr->last_name ?? '',
                    'prescription_id' => $presc->id // Store prescription ID
                ];
                $visitNo++;
            }
        }
        $visitCount = count($otherPrescription);

        $ChiefComplain = ChiefComplain::where('status', 1)->pluck('value', 'id');

        $ipdAdmission = PrescribeIpdAdmission::where('prescription_id', $prescription->id)->first();

        $common_settings = session()->get('business.common_settings');

        return view('clinic::prescriptions.template.loaded_temp_view', compact(
            'patient',
            'advices',
            'dosages',
            'meals',
            'durations',
            'complains',
            'templates',
            'prescription',
            'prescribedMedicines',
            'prescribedTest',
            'prescribedTherapy',
            'existingAdvice',
            'existingComplainIds',
            'prescribedComplain',
            'otherPrescription',
            'visitCount',
            'appointment',
            'data',
            'frequencies',
            'ChiefComplain',
            'diseaseHistories',
            'InvestigationHistory',
            'common_settings',
            'templateId',
            'ipdAdmission'

        ));
    }
    public function fitConvertCM()
    {
        return view('clinic::prescriptions.template.fitToCm');
    }
    public function DoctorProfileSummary(Request $request)
    {
        if (!auth()->user()->can('admin')) {
            abort(403, 'Unauthorized action.');
        }

        $doctor_id = $request->input('doctor_id');
        $date_range = $request->input('date_range');
        $start_date = Carbon::now()->subDays(6)->format('Y-m-d'); // 7 days including today
        $end_date = Carbon::now()->format('Y-m-d');

        if ($date_range) {
            $date_range = explode(' - ', $date_range);
            $start_date = Carbon::createFromFormat('d-m-Y', trim($date_range[0]))->format('Y-m-d');
            $end_date = Carbon::createFromFormat('d-m-Y', trim($date_range[1]))->format('Y-m-d');
        }

        $doctorProfile = $doctor_id ? DoctorProfile::find($doctor_id) : null;

        $prescription = Prescription::query();

        if ($doctorProfile) {
            $prescription->where('doctor_user_id', $doctorProfile->user_id);
        }

        if ($start_date && $end_date) {
            $prescription->whereBetween('prescription_date', [$start_date, $end_date]);
        }

        $patientsSeenCount = $prescription->count();
        // Get the IDs of the prescriptions based on the query
        $prescriptionIds = $prescription->pluck('id');
        $patientProfileIds = $prescription->pluck('patient_profile_id');

        // Now, count the total advice given based on these prescription IDs
        $totalAdviceGiven = PrescribeAdvice::whereIn('prescription_id', $prescriptionIds)->count();

        // Get the average waiting time for each prescription like this averageWaitingTime = prescription created_at - patient appointment request created_at
        $averageWaitingTime = Prescription::leftJoin('patient_appointment_requests', 'patient_appointment_requests.id', '=', 'prescriptions.appointment_id')
            ->where('patient_appointment_requests.remarks', 'prescribed')
            ->whereIn('prescriptions.id', $prescriptionIds)
            ->select(DB::raw('AVG(GREATEST(0, TIMESTAMPDIFF(MINUTE, patient_appointment_requests.confirm_time, prescriptions.created_at))) AS average_waiting_time'))
            ->first();

        // Access the value from the result correctly
        $averageWaitingTime = $averageWaitingTime->average_waiting_time ?? 0;
        $adviceSummary = PrescribeAdvice::whereIn('prescription_id', $prescriptionIds)->select('advise_name', DB::raw('count(*) as count'))
            ->groupBy('advise_name')->orderBy('count', 'DESC')->get();

        $ipdSummary = PrescribeIpdAdmission::whereIn('prescription_id', $prescriptionIds)
            ->where('is_ipd_admission', 1)
            ->select('admission_days', DB::raw('count(*) as count'))
            ->groupBy('admission_days')->orderBy('count', 'DESC')->get();

        $highestWaitingTimes = Prescription::leftJoin('patient_appointment_requests', 'patient_appointment_requests.id', '=', 'prescriptions.appointment_id')
            ->join('patient_profiles', 'patient_profiles.id', '=', 'patient_appointment_requests.patient_profile_id')
            ->where('patient_appointment_requests.remarks', 'prescribed')
            ->whereIn('prescriptions.id', $prescriptionIds)
            ->select(
                'patient_profiles.first_name',
                'patient_profiles.mobile',
                'patient_appointment_requests.confirm_time',
                DB::raw('TIMESTAMPDIFF(MINUTE, patient_appointment_requests.confirm_time, prescriptions.created_at) AS waiting_time')
            )
            ->orderByDesc('waiting_time')
            ->get();

        $therapySummary = PrescribeTherapie::whereIn('prescription_id', $prescriptionIds)->select('therapy_name', DB::raw('count(*) as count'))
            ->groupBy('therapy_name')->orderBy('count', 'DESC')->get();

        $investigationSummary = PrescribeTest::whereIn('prescription_id', $prescriptionIds)->select('test_name', DB::raw('count(*) as count'))
            ->groupBy('test_name')->orderBy('count', 'DESC')->get();

        $patientHealthConsernCategory = PatientDisease::leftJoin('problems', 'patient_diseases.disease_id', '=', 'problems.id')
            ->leftJoin('categories', 'problems.category_id', '=', 'categories.id')
            ->whereIn('patient_diseases.patient_profile_id', $patientProfileIds)
            ->select(DB::raw('COALESCE(categories.name, "Uncategorized") as category_name'), DB::raw('COUNT(DISTINCT patient_diseases.patient_profile_id) as count'))
            ->groupBy('category_name')
            ->get();

        $patientProfiles = PatientProfile::whereIn('id', $patientProfileIds)
            ->select(
                DB::raw('FLOOR(age / 10) * 10 AS age_group'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('age_group')
            ->orderBy('age_group')
            ->get();

        // Fixed age ranges from 1-100
        $ageRanges = collect();
        for ($age = 0; $age <= 90; $age += 10) {
            $ageRanges->push([
                'age_group' => $age,
                'label' => ($age + 1) . ' - ' . ($age + 10)
            ]);
        }

        // Merge with patient counts
        $ageGroups = $ageRanges->map(function ($range) use ($patientProfiles) {
            $match = $patientProfiles->firstWhere('age_group', $range['age_group']);
            return [
                'label' => $range['label'],
                'count' => $match ? $match->count : 0
            ];
        });

        // Get all divisions
        $allDivisions = Division::select('name')->orderBy('name')->get();

        // Get patient counts per division
        $patientDivisions = PatientProfile::whereIn('patient_profiles.id', $patientProfileIds)
            ->join('districts', 'districts.id', '=', 'patient_profiles.district_id')
            ->leftJoin('divisions', 'divisions.id', '=', 'districts.division_id')
            ->select('divisions.name as division', DB::raw('COUNT(*) as count'))
            ->groupBy('divisions.name')
            ->orderBy('divisions.name')
            ->get();

        // Merge counts with all divisions (default count to 0 if no patients)
        $divisionData = $allDivisions->map(function ($division) use ($patientDivisions) {
            $match = $patientDivisions->firstWhere('division', $division->name);
            return [
                'label' => $division->name,
                'count' => $match ? $match->count : 0
            ];
        });
        // Extract labels and counts for chart.js
        $divisionLabels = $divisionData->pluck('label');
        $divisionCounts = $divisionData->pluck('count');

        // Get the product IDs from the filtered products
        $usProductIds = $this->getProductIdsByCategory(41);
        $bdProductIds = $this->getProductIdsByCategory(22);
        $foreignProductIds = $this->getProductIdsByCategory(54);

        // Query the prescribed medicines
        $usSupplementsSummary = PrescribeMedicine::whereIn('prescription_id', $prescriptionIds)
            ->select('prescribed_medicines.x_medicine_id', 'prescribed_medicines.x_medicine_name', DB::raw('count(*) as count'))
            ->whereIn('x_medicine_id', $usProductIds)
            ->groupBy('x_medicine_name')
            ->orderBy('count', 'DESC')
            ->get();

        $bdMedicinSummary = PrescribeMedicine::whereIn('prescription_id', $prescriptionIds)
            ->select('prescribed_medicines.x_medicine_id', 'prescribed_medicines.x_medicine_name', DB::raw('count(*) as count'))
            ->whereIn('x_medicine_id', $bdProductIds)
            ->groupBy('x_medicine_name')
            ->orderBy('count', 'DESC')
            ->get();

        $foreignSupplementsSummary = PrescribeMedicine::whereIn('prescription_id', $prescriptionIds)
            ->select('prescribed_medicines.x_medicine_id', 'prescribed_medicines.x_medicine_name', DB::raw('count(*) as count'))
            ->whereIn('x_medicine_id', $foreignProductIds)
            ->groupBy('x_medicine_name')
            ->orderBy('count', 'DESC')
            ->get();

        $investigationsNotInSoftware = MissingTest::whereIn('prescription_id', $prescriptionIds)->select('name', DB::raw('count(*) as count'))
            ->groupBy('name')->orderBy('count', 'DESC')->get();

        $medicinesNotInSoftware = MissingMedicine::whereIn('prescription_id', $prescriptionIds)->select('name', DB::raw('count(*) as count'))
            ->groupBy('name')->orderBy('count', 'DESC')->get();

        $therapiesNotInSoftware = MissingTherapy::whereIn('prescription_id', $prescriptionIds)->select('name', DB::raw('count(*) as count'))
            ->groupBy('name')->orderBy('count', 'DESC')->get();

        $result = $this->getProductBrand($prescriptionIds);

        $brandSummary = $result['brand_summary'];

        // Extract labels and counts for the chart.js
        $brandLabels = array_keys($brandSummary);
        $medicineCounts = array_values($brandSummary);

        $doctors = DoctorProfile::selectRaw("CONCAT(first_name, IF(last_name IS NOT NULL AND last_name != '', CONCAT(' ', last_name), '')) as full_name, id")
            ->pluck('full_name', 'id');


        return view('clinic::prescriptions.doctor_profile_summary', compact(
            'doctors',
            'patientsSeenCount',
            'totalAdviceGiven',
            'averageWaitingTime',
            'highestWaitingTimes',
            'adviceSummary',
            'ipdSummary',
            'therapySummary',
            'investigationSummary',
            'usSupplementsSummary',
            'foreignSupplementsSummary',
            'medicinesNotInSoftware',
            'therapiesNotInSoftware',
            'investigationsNotInSoftware',
            'patientHealthConsernCategory',
            'ageGroups',
            'divisionLabels',
            'divisionCounts',
            'bdMedicinSummary',
            'brandLabels',
            'medicineCounts',
            'start_date',
            'end_date'
        ));
    }

    public function missingTestDetails($testName)
    {
        $missingTests = MissingTest::leftJoin('prescriptions', 'prescriptions.id', '=', 'missing_tests.prescription_id')
            ->leftjoin('patient_profiles', 'patient_profiles.id', '=', 'prescriptions.patient_profile_id')
            ->leftjoin('users', 'users.id', '=', 'prescriptions.doctor_user_id')
            ->where('missing_tests.name', $testName)
            ->select(
                'missing_tests.*',
                'missing_tests.name as test_name',
                'prescriptions.*',
                'patient_profiles.first_name as patient_first_name',
                'patient_profiles.last_name as patient_last_name',
                'users.first_name as doctor_first_name',
                'users.last_name as doctor_last_name'
            )->get();

        Log::info($missingTests);

        return view('clinic::prescriptions.missing_test', compact('missingTests'));
    }
    public function missingMedicineDetails($medicineName)
    {
        $missingMedicines = MissingMedicine::leftJoin('prescriptions', 'prescriptions.id', '=', 'missing_medicines.prescription_id')
            ->leftjoin('patient_profiles', 'patient_profiles.id', '=', 'prescriptions.patient_profile_id')
            ->leftjoin('users', 'users.id', '=', 'prescriptions.doctor_user_id')
            ->where('missing_medicines.name', $medicineName)
            ->select(
                'missing_medicines.*',
                'missing_medicines.name as madicine_name',
                'prescriptions.*',
                'patient_profiles.first_name as patient_first_name',
                'patient_profiles.last_name as patient_last_name',
                'users.first_name as doctor_first_name',
                'users.last_name as doctor_last_name'
            )->get();

        return view('clinic::prescriptions.missing_medicine', compact('missingMedicines'));
    }
    public function missingTherapyDetails($therapyName)
    {
        $missingTherapy = MissingTherapy::leftJoin('prescriptions', 'prescriptions.id', '=', 'missing_therapies.prescription_id')
            ->leftjoin('patient_profiles', 'patient_profiles.id', '=', 'prescriptions.patient_profile_id')
            ->leftjoin('users', 'users.id', '=', 'prescriptions.doctor_user_id')
            ->where('missing_therapies.name', $therapyName)
            ->select(
                'missing_therapies.*',
                'missing_therapies.name as therapy_name',
                'prescriptions.*',
                'patient_profiles.first_name as patient_first_name',
                'patient_profiles.last_name as patient_last_name',
                'users.first_name as doctor_first_name',
                'users.last_name as doctor_last_name'
            )->get();

        return view('clinic::prescriptions.missing_therapy', compact('missingTherapy'));
    }

    private function fetchProductsFromApi()
    {
        return Cache::remember('api_products', now()->addMinutes(10), function () {
            try {
                $apiResponse = Http::retry(3, 500) // 🔄 Retries 3 times with 500ms delay
                    ->withoutVerifying()
                    ->get('https://203.190.9.99:82/api/products');

                if ($apiResponse->failed()) {
                    throw new \Exception("API request failed with status code: " . $apiResponse->status());
                }

                $products = json_decode($apiResponse->getBody(), true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception("Failed to decode JSON response: " . json_last_error_msg());
                }

                return $products;
            } catch (\Exception $e) {
                Log::error("Error fetching products from API: " . $e->getMessage());
                return []; // Return empty array to avoid breaking app
            }
        });
    }
    /**
     * Get product IDs by category.
     *
     * @param int $categoryId
     * @return array
     * @throws \Exception
     */
    public function getProductIdsByCategory($categoryId)
    {
        $products = $this->fetchProductsFromApi(); // Cached API data

        return array_column(
            array_filter($products, fn($product) => $product['category_id'] == $categoryId),
            'product_id'
        );
    }

    /**
     * Get product brand summary.
     *
     * @param array $prescriptionIds
     * @return array
     * @throws \Exception
     */
    public function getProductBrand($prescriptionIds)
    {
        $categoryId = 22;
        $bdProductIds = $this->getProductIdsByCategory($categoryId);

        $bdMedicinSummary = PrescribeMedicine::whereIn('prescription_id', $prescriptionIds)
            ->whereIn('x_medicine_id', $bdProductIds)
            ->get(['x_medicine_id']);

        // 🔹 Fetch API data only once (cached)
        $products = $this->fetchProductsFromApi();

        // 🔹 Create Product ID → Brand map
        $productIdToBrandMap = collect($products)->mapWithKeys(fn($p) => [
            $p['product_id'] => $p['brand']['name'] ?? 'Unknown Brand'
        ])->toArray();

        // 🔹 Count medicines by brand
        $brandSummary = [];
        foreach ($bdMedicinSummary as $medicine) {
            $brandName = $productIdToBrandMap[$medicine->x_medicine_id] ?? 'Unknown Brand';
            $brandSummary[$brandName] = ($brandSummary[$brandName] ?? 0) + 1;
        }

        return ['brand_summary' => $brandSummary];
    }


    public function DoctorsComparativeKPIReport(Request $request)
    {
        if (!auth()->user()->can('admin') && !auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        $start_date = Carbon::now()->subDays(29)->format('Y-m-d'); // 30 days including today
        $end_date = Carbon::now()->format('Y-m-d');

        if ($request->input('start_date') && $request->input('end_date')) {
            $start_date = Carbon::parse($request->input('start_date'))->format('Y-m-d');
            $end_date = Carbon::parse($request->input('end_date'))->format('Y-m-d');
        }

        $doctors = DoctorProfile::get();

        $reportData = [];

        foreach ($doctors as $doctor) {
            $prescriptions = Prescription::where('doctor_user_id', $doctor->user_id)
                ->whereBetween('prescription_date', [$start_date, $end_date])
                ->get();

            $patientsSeen = $prescriptions->count();

            $averageWaitingTime = Prescription::leftJoin('patient_appointment_requests', 'patient_appointment_requests.id', '=', 'prescriptions.appointment_id')
                ->where('patient_appointment_requests.remarks', 'prescribed')
                ->whereIn('prescriptions.id', $prescriptions->pluck('id'))
                ->select(DB::raw('AVG(GREATEST(0, TIMESTAMPDIFF(MINUTE, patient_appointment_requests.confirm_time, prescriptions.created_at))) AS average_waiting_time'))
                ->first();

            $hours = floor($averageWaitingTime->average_waiting_time / 60);
            $minutes = $averageWaitingTime->average_waiting_time % 60;
            $formattedTime = $hours > 0 ? "{$hours} hr {$minutes} min" : "{$minutes} min";

            $therapies = PrescribeTherapie::whereIn('prescription_id', $prescriptions->pluck('id'))->count();
            $avgTherapiesPerPatient = $patientsSeen > 0 ? $therapies / $patientsSeen : 0;

            $investigations = PrescribeTest::whereIn('prescription_id', $prescriptions->pluck('id'))->count();
            $avgInvestigationsPerPatient = $patientsSeen > 0 ? $investigations / $patientsSeen : 0;

            $usSupplements = PrescribeMedicine::whereIn('prescription_id', $prescriptions->pluck('id'))->whereIn('x_medicine_id', $this->getProductIdsByCategory(41))->count();
            $avgUSSupplementsPerPatient = $patientsSeen > 0 ? $usSupplements / $patientsSeen : 0;

            $foreignSupplements = PrescribeMedicine::whereIn('prescription_id', $prescriptions->pluck('id'))->whereIn('x_medicine_id', $this->getProductIdsByCategory(54))->count();
            $avgForeignSupplementsPerPatient = $patientsSeen > 0 ? $foreignSupplements / $patientsSeen : 0;

            $bdMedicines = PrescribeMedicine::whereIn('prescription_id', $prescriptions->pluck('id'))->whereIn('x_medicine_id', $this->getProductIdsByCategory(22))->count();
            $avgBDMedicinesPerPatient = $patientsSeen > 0 ? $bdMedicines / $patientsSeen : 0;

            $reportData[] = [
                'doctor' => $doctor->first_name . ' ' . $doctor->last_name,
                'patients_seen' => $patientsSeen,
                'avg_waiting_time' => $formattedTime,
                'therapies' => $therapies,
                'avg_therapy_per_patient' => $avgTherapiesPerPatient,
                'investigations' => $investigations,
                'avg_investigation_per_patient' => $avgInvestigationsPerPatient,
                'us_supplements' => $usSupplements,
                'avg_us_supplement_per_patient' => $avgUSSupplementsPerPatient,
                // 'foreign_supplements' => $foreignSupplements,
                // 'avg_foreign_supplement_per_patient' => $avgForeignSupplementsPerPatient,
                'bd_medicines' => $bdMedicines,
                'avg_bd_medicine_per_patient' => $avgBDMedicinesPerPatient,
            ];
        }
        //if ajax request then return json response
        if ($request->ajax()) {
            return response()->json($reportData);
        }

        return view('clinic::prescriptions.doctor_comparative_kpi', compact('reportData', 'start_date', 'end_date'));
    }

    public function NextVisitData(Request $request)
    {
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $call_by = $request->input('call_by');
        $business_id = request()->session()->get('user.business_id');

        if ($request->ajax()) {
            $query = Prescription::join('doctor_profiles', 'prescriptions.doctor_user_id', '=', 'doctor_profiles.user_id')
                ->join('patient_profiles', 'prescriptions.patient_profile_id', '=', 'patient_profiles.id')
                ->join('patient_session_info', 'patient_profiles.patient_contact_id', '=', 'patient_session_info.patient_contact_id')
                ->join('session_information', 'patient_session_info.session_id', '=', 'session_information.id')
                ->join('patient_appointment_requests', 'patient_profiles.patient_contact_id', '=', 'patient_appointment_requests.patient_contact_id')
                ->leftJoin('users', 'prescriptions.call_by', '=', 'users.id')
                // ->where('patient_session_info.visited_count', '!=', 0)
                ->select(
                    'patient_profiles.id as patient_profile_id',
                    'patient_profiles.patient_contact_id as patient_contact_id',
                    'doctor_profiles.first_name as doctor_first_name',
                    'doctor_profiles.last_name as doctor_last_name',
                    'patient_profiles.first_name as patient_first_name',
                    'patient_profiles.last_name as patient_last_name',
                    'patient_profiles.mobile',
                    'prescriptions.next_visit_date',
                    'prescriptions.call_status',
                    'prescriptions.call_note',
                    'prescriptions.id as prescription_id',
                    'session_information.session_name',
                    'patient_appointment_requests.remarks',
                    DB::raw("CONCAT(users.first_name, ' ', IFNULL(users.last_name, '')) as call_by"),
                )->orderBy('prescriptions.next_visit_date', 'asc');

            // ✅ Fix: Apply date filter before fetching data
            if (!empty($start_date) && !empty($end_date)) {
                $query->whereBetween('prescriptions.next_visit_date', [$start_date, $end_date]);
            }

            // ✅ Fix: Apply call_by filter and next_visit_date filter for today and the next 2 days
            if (!empty($call_by)) {
                $query->where('prescriptions.call_by', $call_by);
            }

            $call_statuses = $this->call_statuses();

            return Datatables::of($query)
                ->addColumn('action', function ($row) {
                    return '<a href="' . action([\Modules\Clinic\Http\Controllers\PatientController::class, 'profile'], [$row->patient_profile_id]) . '" ><i class="fas fa-eye" aria-hidden="true"></i> View </a>';
                })
                ->editColumn('doctor_name', function ($row) {
                    return $row->doctor_first_name . ' ' . $row->doctor_last_name;
                })
                ->editColumn('patient_name', function ($row) {
                    return '<div style="display: flex; align-items: center;">
                                <i class="fas fa-user-circle text-primary" style="font-size: 30px; margin-right: 8px;"></i> 
                                <div>
                                    <strong>' . ($row->patient_first_name ?? '') . ' ' . ($row->patient_last_name ?? '') . '</strong>' . '(' . ($row->mobile ?? '') . ')'
                        . '<br> <small class="text-muted">PID: ' . ($row->patient_contact_id ?? 'N/A') . '</small>
                                </div>
                            </div>';
                })
                ->editColumn('next_visit_date', function ($row) {
                    return $row->next_visit_date ? date('d/m/Y', strtotime($row->next_visit_date)) : 'N/A';
                })
                ->editColumn('status', function ($row) {
                    return $row->remarks;
                })
                ->editColumn('call_status', function ($row) use ($call_statuses) {
                    $status_color = $this->call_status_colors()[$row->call_status] ?? 'bg-gray';
                    return $row->call_status ? "<a href='#' class='cursor-pointer edit-call-status' data-href='" . action([PrescriptionsController::class, 'editCallStatus'], $row->prescription_id) . "' data-container='.view_modal'><span class='label {$status_color}'>{$call_statuses[$row->call_status]}</span></a>" : '';
                })
                ->editColumn('call_note', function ($row) {
                    return $row->call_note ?? '';
                })
                ->editColumn('call_by', function ($row) {
                    return $row->call_by ?? '';
                })
                ->addColumn('patient_type', function ($row) {
                    return $row->session_name;
                })
                ->rawColumns(['doctor_name', 'patient_name', 'next_visit_date', 'action', 'patient_type', 'call_status', 'status'])
                ->make(true);
        }

        $users = User::forDropdown($business_id);

        return view('clinic::report.next_visit_data', compact('users'));
    }

    protected function call_status_colors()
    {
        return [
            'pending' => 'bg-yellow',
            'no_response' => 'bg-info',
            'switch_off' => 'bg-navy',
            'away' => 'bg-green',
            'busy' => 'bg-red',
            'done' => 'bg-blue',
        ];
    }
    protected function call_statuses()
    {
        return [
            'pending' => __('lang_v1.pending'),
            'no_response' => __('lang_v1.no_response'),
            'switch_off' => __('lang_v1.switch_off'),
            'away' => __('lang_v1.away'),
            'busy' => __('lang_v1.busy'),
            'done' => __('lang_v1.done'),
        ];
    }

    public function editCallStatus($id)
    {
        $prescription = Prescription::findOrFail($id);
        $statuses = $this->call_statuses();
        return view('clinic::report.call_status_edit_modal', compact('prescription', 'statuses'));
    }

    public function updateCallStatus(Request $request)
    {
        $prescription = Prescription::findOrFail($request->id);
        $prescription_before = clone $prescription;
        $prescription->call_status = $request->call_status;
        $prescription->call_note = $request->call_note;
        $prescription->call_by = auth()->user()->id;
        $prescription->save();

        $this->prescriptionUtil->activityLog($prescription, 'call_status_edited', $prescription_before);

        return response()->json(['success' => __('messages.updated_success')]);
    }
}
