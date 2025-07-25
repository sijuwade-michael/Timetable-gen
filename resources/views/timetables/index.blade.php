{{-- @extends('layouts.app')

@section('content')
<div class="container">
    <h1>Weekly Timetable</h1>

    <a href="{{ route('timetable.export') }}" class="btn btn-success mb-3">Export to Excel</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Day</th>
                <th>Time</th>
                <th>Course</th>
                <th>Lecturer</th>
                <th>Venue</th>
                <th>Faculty</th>
            </tr>
        </thead>
        <tbody>
            @foreach($timetables as $timetable)
                <tr>
                    <td>{{ $timetable->period->day }}</td>
                    <td>{{ $timetable->period->label }}</td>
                    <td>{{ $timetable->course->title }}</td>
                    <td>{{ $timetable->course->lecturer->name ?? 'N/A' }}</td>
                    <td>{{ $timetable->venue->name }}</td>
                    <td>{{ $timetable->faculty->name }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection --}}
