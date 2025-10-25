<?php 
use Illuminate\Support\Facades\Route;
use Modules\Clinic\Http\Controllers\Survey\{
    ReviewReportController,
    DoctorController,
    CommentController,
    ProblemController,
    IntakeFormController,
    ReferenceDoctorController,
    ReferenceController,
};
Route::get('/', function () {
    return view('clinic::survey.dashboard');
});

    Route::get('/addDoctorView', [DoctorController::class, 'create'])->name('doctor.add');
    Route::post('/addDoctor', [DoctorController::class, 'store']);


    // Route::get('/editDoctor/{id}', [DoctorController::class, 'edit']);
    // Route::post('/updateDoctor', [DoctorController::class, 'update'])->name('doctor.update');
    // Route::get('/patient', [PatientController::class, 'index'])->name('patient');
    // Route::get('/getPatient', [PatientController::class, 'getPatient'])->name('patient_profiles.index');
    // Route::get('/addPatient', [PatientController::class, 'create'])->name('add.patient');
    // Route::post('/addNewPatient', [PatientController::class, 'store'])->name('add.new.patient');
    // Route::get('/editPatient/{id}', [PatientController::class, 'edit']);
    // Route::delete('/deletePatient/{id}', [PatientController::class, 'destroy']);
    // Route::get('/getOnePatient/{id}', [PatientController::class, 'getOnePatient']);
    // Route::post('/updatePatient', [PatientController::class, 'update'])->name('update.patient.info');
    // Route::delete('/deleteDoctor/{id}', [DoctorController::class, 'destroy']);
    // Route::get('/viewDoctor/{id}/{days}', [DoctorController::class, 'view']);

    Route::get('/doctor', [DoctorController::class, 'index'])->name('doctor');
    Route::get('/doctor_profiles', [DoctorController::class, 'show'])->name('doctor_profiles.index');
    // Route::get('roporting', function(){
    //     return view('medical_report.add');
    // });

    // Route::resource('medical-tests', MedicalTestController::class);
    // Route::resource('patient-medical-tests', PatientMedicalTestController::class);
    // Route::get('/tests/edit/{id}', [MedicalTestController::class, 'editview']);
    // Route::get('create/medical/report', [ReviewReportController::class, 'creating'])->name('add.new.patient.report');
    Route::get('latest/report/{id}', [ReviewReportController::class, 'latestReport'])->name('report.latest');
    Route::resource('medical-report', ReviewReportController::class);
    Route::get('print/intake/form/{id}', [ReviewReportController::class, 'printIntakeform'])->name('print.intake.form');
    Route::get('/feedback/kpi/report', [ReviewReportController::class, 'feedbackKPIReport'])->name('feedback.kpi.report');
    // Route::get('getupozilla/{id}', [PatientController::class, 'upozilla' ])->name('get.upozilla');
    Route::resource('problems', ProblemController::class);
    Route::get('problem/wise/patient', [ProblemController::class, 'problemWisePatient'])->name('problem.wise.patient');
    Route::get('problem/wise/patient/new', [ProblemController::class, 'problemWisePatientNew'])->name('problem.wise.patient.new');
    Route::get('/problem-wise-patient-chart', [ProblemController::class, 'problemWisePatientChart'])->name('problem.wise.patient.chart');
    Route::get('/problem-wise-patient-chart-filter', [ProblemController::class, 'problemWisePatientChartFilter'])->name('problem.wise.patient.chart.filter');
    Route::get('comment/wise/patient', [CommentController::class, 'commentWisePatient'])->name('comment.wise.patient');
    Route::get('comment/wise/patient/new/report', [CommentController::class, 'commentWisePatientNewFormat'])->name('comment.wise.patient.new_report');
    Route::get('/comment-wise-patient-chart-filter', [CommentController::class, 'commentWisePatientChartFilter'])->name('comment.wise.patient.chart.filter');
    Route::resource('survey-references', ReferenceController::class);
    Route::get('source-index', [ReferenceController::class, 'sourceIndex'])->name('survey-sources.index');
    Route::get('get-sub-source/{id}', [ReferenceController::class, 'getSubSource']);
    Route::get('/reference-wise-patient-chart-filter', [ReferenceController::class, 'referenceWisePatientChartFilter'])->name('reference.wise.patient.chart.filter');

    // Route::get('/medical-tests-list', [MedicalTestController::class, 'getMedicalTests'])->name('medical-tests.list');
    // Route::resource('report', ReportController::class);
    // Route::resource('role', RoleController::class);

    // Route::resource('patient-profile', PatientProfileController::class);
    Route::resource('patient-comment', CommentController::class);
    Route::resource('reference-doctor', ReferenceDoctorController::class);
    Route::get('reference-wise-patient', [ReferenceController::class, 'referenceWisePatient'])->name('reference.wise.patient');
    Route::resource('intake-form', IntakeFormController::class);
    Route::get('print/after/store/{id}', [IntakeFormController::class, 'printAfterStore'])->name('print.after.store');
    Route::get('print/intake/form/{id}', [IntakeFormController::class, 'printIntakeform'])->name('print.intake.form');
