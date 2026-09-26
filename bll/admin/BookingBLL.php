<?php
// bll/admin/BookingBLL.php
require_once '../../dal/admin/BookingDAL.php';

class BookingBLL {
    private $dal;
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->dal = new BookingDAL($pdo);
    }

    // Get facilities list for view rendering
    public function getFacilities() {
        try {
            return $this->dal->getAllFacilities();
        } catch (Exception $e) {
            return [];
        }
    }

    // Generate schedule grid data
    public function getScheduleGrid($facility_id, $shift, $start_date) {
        $times = $shift === 'morning' ? 
            ['06:00:00', '07:00:00', '08:00:00', '09:00:00', '10:00:00', '11:00:00'] : 
            ['12:00:00', '13:00:00', '14:00:00', '15:00:00', '16:00:00', '17:00:00'];

        $capacity = $this->dal->getFacilityCapacity($facility_id);
        $grid = [];

        foreach ($times as $time) {
            $display_time = date('H:i', strtotime($time));
            $row = [$display_time];
            
            for ($i = 0; $i < 7; $i++) {
                $current_date = date('Y-m-d', strtotime($start_date . " +$i days"));
                
                try {
                    $total_booked = $this->dal->getBookedSize($facility_id, $current_date, $time);
                    $occupancy = ($capacity > 0) ? round(($total_booked / $capacity) * 100) : 0;
                    $occupancy = ($occupancy > 100) ? 100 : $occupancy;

                    $row[] = $occupancy;
                } catch (Exception $e) {
                    $row[] = 0; 
                }
            }
            $grid[] = $row;
        }

        return ['success' => true, 'capacity' => $capacity, 'gridData' => $grid];
    }

    // Get specific slot bookings
    public function getSlotDetails($facility_id, $date, $time) {
        try {
            $bookings = $this->dal->getSlotBookings($facility_id, $date, $time);
            return ['success' => true, 'bookings' => $bookings];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // Get pending requests list
    public function getPendingRequests() {
        try {
            $requests = $this->dal->getPendingRequests();
            return ['success' => true, 'requests' => $requests];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // Check capacity conflicts
    public function checkConflict($booking_id) {
        try {
            $pending = $this->dal->getBookingById($booking_id);
            if (!$pending) {
                return ['success' => false, 'error' => 'Booking not found'];
            }

            $max_capacity = $this->dal->getFacilityCapacity($pending['Facility_ID']);
            $current_booked = (int)$this->dal->getBookedSize($pending['Facility_ID'], $pending['Reserve_Date'], $pending['Start_Time']);

            $new_total = $current_booked + (int)$pending['Team_Size'];

            if ($new_total > $max_capacity) {
                $occupants = $this->dal->getOccupants($pending['Facility_ID'], $pending['Reserve_Date'], $pending['Start_Time']);

                return [
                    'success' => true,
                    'has_conflict' => true,
                    'max_capacity' => $max_capacity,
                    'current_booked' => $current_booked,
                    'pending_size' => $pending['Team_Size'],
                    'overflow' => $new_total - $max_capacity,
                    'occupants' => $occupants
                ];
            } else {
                return ['success' => true, 'has_conflict' => false];
            }
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // Approve booking process
    public function approveBooking($booking_id, $admin_id, $cancel_ids, $cancel_reason) {
        try {
            $this->dal->approveAndOverride($booking_id, $admin_id, $cancel_ids, $cancel_reason);
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // Decline booking process
    public function declineBooking($booking_id, $admin_id, $reject_reason) {
        try {
            $this->dal->declineBooking($booking_id, $admin_id, $reject_reason);
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}