<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Project;
use App\Models\SerialNumber;
use App\Models\Bug;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Seeded Users from Production Names
        $userNames = [
            'Admin' => ['email' => 'admin@bugtrack.test', 'role' => 'admin'],
            'Fioni Agriyani' => ['email' => 'fioni@bugtrack.test', 'role' => 'mekanik'],
            'Maneng' => ['email' => 'maneng@bugtrack.test', 'role' => 'mekanik'],
            'manufacture' => ['email' => 'manufacture@bugtrack.test', 'role' => 'mekanik'],
            'program' => ['email' => 'program@bugtrack.test', 'role' => 'mekanik'],
            '1' => ['email' => 'user1@bugtrack.test', 'role' => 'mekanik'],
        ];

        $users = [];
        foreach ($userNames as $name => $data) {
            $users[strtolower($name)] = User::create([
                'name' => $name,
                'email' => $data['email'],
                'password' => Hash::make('password123'),
                'role' => $data['role'],
            ]);
        }

        // Add a testing reporter account
        $users['reporter'] = User::create([
            'name' => 'Reporter Testing',
            'email' => 'reporter@bugtrack.test',
            'password' => Hash::make('password123'),
            'role' => 'reporter',
        ]);

        // 2. Create Master Projects (1, 3, 27, 31, 176, 177, 189, 192)
        $projectIds = [1, 3, 27, 31, 176, 177, 189, 192];
        foreach ($projectIds as $pId) {
            Project::create([
                'id' => $pId,
                'name' => "Project #$pId",
            ]);
        }

        // 3. Create Master Serial Numbers
        $sns = [
            ['id' => 4, 'project_id' => 27, 'sn_code' => 'SN_UNIT_TACA#OPSHYB_2026-001', 'type' => 'unit'],
            ['id' => 15, 'project_id' => 1, 'sn_code' => 'SUB_PN_TACA#OPSHYB_2026-1-001', 'type' => 'sub'],
            ['id' => 13, 'project_id' => 3, 'sn_code' => 'SUB_PN_TACA#OPSHYB_2026-2-001', 'type' => 'sub'],
            ['id' => 21, 'project_id' => 1, 'sn_code' => 'SUB_PN_TACA#OPSHYB_2026-1-003', 'type' => 'sub'],
            ['id' => 10300, 'project_id' => 176, 'sn_code' => '1.1', 'type' => 'sub'],
            ['id' => 12, 'project_id' => 31, 'sn_code' => 'SN-SLR-10W-01', 'type' => 'unit'],
            ['id' => 17, 'project_id' => 27, 'sn_code' => 'SN_UNIT_TACA#OPSHYB_2026-002', 'type' => 'unit'],
            ['id' => 10527, 'project_id' => 27, 'sn_code' => 'SN_UNIT_TACA#OPSHYB_2026-003', 'type' => 'unit'],
            ['id' => 10302, 'project_id' => 177, 'sn_code' => 'SUB_PN_TACA#OPSHYB_2026-3-001', 'type' => 'sub'],
            ['id' => 10303, 'project_id' => 177, 'sn_code' => 'SUB_PN_TACA#OPSHYB_2026-3-002', 'type' => 'sub'],
            ['id' => 10301, 'project_id' => 176, 'sn_code' => 'SUB_PN_TACA#OPSHYB_2026-1-002', 'type' => 'sub'],
            ['id' => 10541, 'project_id' => 189, 'sn_code' => 'SUB_PN_TACA#OPSHYB_2026-4-001', 'type' => 'sub'],
            ['id' => 10559, 'project_id' => 192, 'sn_code' => 'sn-contoh-001.1', 'type' => 'sub'],
            ['id' => 10560, 'project_id' => 192, 'sn_code' => 'sn-contoh-001.2', 'type' => 'sub'],
            ['id' => 10561, 'project_id' => 192, 'sn_code' => 'sn-contoh-001.3', 'type' => 'sub'],
        ];

        foreach ($sns as $sn) {
            SerialNumber::create($sn);
        }

        // 4. Import the 24 Bug Rows
        $bugsData = [
            [
                'id' => 17, 'project_id' => 27, 'title' => 'DCDC short hubung singkat', 'severity' => 'Major',
                'serial_number_id' => 4, 'sn_code_snapshot' => 'SN_UNIT_TACA#OPSHYB_2026-001', 'reporter_type' => 'produk',
                'description' => 'DCDC short karena hubung singkat', 'product_version' => 'v.20260224', 'environment' => 'penggunaan di posko, kondisi panas',
                'reproduce_steps' => null, 'root_cause' => 'root cause', 'repair_action' => 'diperbaiki', 'is_rework' => 0, 'attachment_path' => null,
                'expected_result' => null, 'reported_by' => 'Admin', 'status' => 'CLOSED', 'fixed_by' => 'Admin',
                'closed_at' => '2026-03-04 16:36:06', 'created_at' => '2026-02-24 16:52:52', 'updated_at' => '2026-03-16 09:19:49'
            ],
            [
                'id' => 18, 'project_id' => 1, 'title' => 'taca terbakar', 'severity' => 'Major',
                'serial_number_id' => 15, 'sn_code_snapshot' => 'SUB_PN_TACA#OPSHYB_2026-1-001', 'reporter_type' => 'sub',
                'description' => 'dcdc short terbakar', 'product_version' => 'v.20260224', 'environment' => 'penggunaan di posko, kondisi panas',
                'reproduce_steps' => 'sfasdfadfadf', 'root_cause' => 'rtououououou', 'repair_action' => 'repapaiaiaiaia', 'is_rework' => 1, 'attachment_path' => null,
                'expected_result' => 'adfadfafdadf', 'reported_by' => 'Admin', 'status' => 'CLOSED', 'fixed_by' => 'Admin',
                'closed_at' => '2026-03-02 00:00:00', 'created_at' => '2026-02-24 17:03:44', 'updated_at' => '2026-03-02 10:15:40'
            ],
            [
                'id' => 19, 'project_id' => 3, 'title' => 'Kapasitor Meledak', 'severity' => 'Critical',
                'serial_number_id' => 13, 'sn_code_snapshot' => 'SUB_PN_TACA#OPSHYB_2026-2-001', 'reporter_type' => 'sub',
                'description' => 'kapasitor meledak karena terlalu panas ', 'product_version' => 'versi optim 20260221', 'environment' => 'vvvadadfad',
                'reproduce_steps' => null, 'root_cause' => null, 'repair_action' => '', 'is_rework' => 0, 'attachment_path' => null,
                'expected_result' => null, 'reported_by' => 'Admin', 'status' => 'OPEN', 'fixed_by' => null,
                'closed_at' => null, 'created_at' => '2026-02-25 17:17:07', 'updated_at' => '2026-03-16 09:18:50'
            ],
            [
                'id' => 21, 'project_id' => 1, 'title' => 'Kapasitor Meledak', 'severity' => 'Major',
                'serial_number_id' => 21, 'sn_code_snapshot' => 'SUB_PN_TACA#OPSHYB_2026-1-003', 'reporter_type' => 'sub',
                'description' => 'kapasitor meledak karena kembung', 'product_version' => 'versi optim 20260221', 'environment' => 'penggunaan di posko, kondisi panas',
                'reproduce_steps' => null, 'root_cause' => null, 'repair_action' => '', 'is_rework' => 0, 'attachment_path' => null,
                'expected_result' => null, 'reported_by' => 'Admin', 'status' => 'OPEN', 'fixed_by' => null,
                'closed_at' => null, 'created_at' => '2026-02-26 10:31:42', 'updated_at' => '2026-03-16 09:18:02'
            ],
            [
                'id' => 22, 'project_id' => 1, 'title' => 'lensa berembun', 'severity' => 'Major',
                'serial_number_id' => 15, 'sn_code_snapshot' => 'SUB_PN_TACA#OPSHYB_2026-1-001', 'reporter_type' => 'sub',
                'description' => 'lensa berembun', 'product_version' => 'versi optim 20260221', 'environment' => 'digunakan di luar ruangan',
                'reproduce_steps' => null, 'root_cause' => '', 'repair_action' => '', 'is_rework' => 0, 'attachment_path' => '1772616270_725ff09cd85df70bbc3a.mp4',
                'expected_result' => null, 'reported_by' => 'Admin', 'status' => 'OPEN', 'fixed_by' => null,
                'closed_at' => null, 'created_at' => '2026-02-26 11:52:55', 'updated_at' => '2026-03-16 09:17:35'
            ],
            [
                'id' => 23, 'project_id' => 1, 'title' => 'Kapasitor Meledak', 'severity' => 'Critical',
                'serial_number_id' => 15, 'sn_code_snapshot' => 'SUB_PN_TACA#OPSHYB_2026-1-001', 'reporter_type' => 'sub',
                'description' => 'kapasitor meledak karena terlalu panas', 'product_version' => 'versi optim 20260221', 'environment' => 'penggunaan di posko, kondisi panas',
                'reproduce_steps' => 'asdfasdfasd', 'root_cause' => '', 'repair_action' => '', 'is_rework' => 1, 'attachment_path' => null,
                'expected_result' => 'asdfasdfasdfasdf', 'reported_by' => 'Admin', 'status' => 'CLOSED', 'fixed_by' => 'Fioni Agriyani',
                'closed_at' => '2026-03-05 11:39:32', 'created_at' => '2026-02-27 14:58:37', 'updated_at' => '2026-03-05 11:39:32'
            ],
            [
                'id' => 24, 'project_id' => 176, 'title' => 'Short', 'severity' => 'Major',
                'serial_number_id' => 10300, 'sn_code_snapshot' => '1.1', 'reporter_type' => 'sub',
                'description' => 'asdfg', 'product_version' => '123', 'environment' => '-',
                'reproduce_steps' => 'asdf', 'root_cause' => 'qqqq', 'repair_action' => 'aaaa', 'is_rework' => 0, 'attachment_path' => null,
                'expected_result' => 'asdf', 'reported_by' => 'Fioni Agriyani', 'status' => 'CLOSED', 'fixed_by' => 'Fioni Agriyani',
                'closed_at' => '2026-03-03 00:00:00', 'created_at' => '2026-03-03 14:36:08', 'updated_at' => '2026-03-03 14:37:59'
            ],
            [
                'id' => 25, 'project_id' => 176, 'title' => 'Short', 'severity' => 'Major',
                'serial_number_id' => 10300, 'sn_code_snapshot' => '1.1', 'reporter_type' => 'sub',
                'description' => 'qwerty', 'product_version' => '111', 'environment' => '111',
                'reproduce_steps' => 'asdfg', 'root_cause' => 'poiuy', 'repair_action' => 'lkjhg', 'is_rework' => 1, 'attachment_path' => null,
                'expected_result' => 'zxcvb', 'reported_by' => 'Fioni Agriyani', 'status' => 'CLOSED', 'fixed_by' => 'Fioni Agriyani',
                'closed_at' => '2026-03-03 00:00:00', 'created_at' => '2026-03-03 14:40:27', 'updated_at' => '2026-03-03 14:40:52'
            ],
            [
                'id' => 26, 'project_id' => 31, 'title' => 'tidak bisa charge', 'severity' => 'Critical',
                'serial_number_id' => 12, 'sn_code_snapshot' => 'SN-SLR-10W-01', 'reporter_type' => 'produk',
                'description' => 'adsfasdfasdfa', 'product_version' => 'versi optim 20260221', 'environment' => 'aadfasdfadfa',
                'reproduce_steps' => 'beberbebrebr', 'root_cause' => null, 'repair_action' => null, 'is_rework' => 0, 'attachment_path' => null,
                'expected_result' => 'alakdslakdlfakdl', 'reported_by' => 'Admin', 'status' => 'OPEN', 'fixed_by' => null,
                'closed_at' => null, 'created_at' => '2026-03-09 20:31:32', 'updated_at' => '2026-03-09 20:31:32'
            ],
            [
                'id' => 27, 'project_id' => 27, 'title' => 'Kapasitor Meledak', 'severity' => 'Critical',
                'serial_number_id' => 4, 'sn_code_snapshot' => null, 'reporter_type' => 'produk',
                'description' => 'kmkkmkmkmaksdmfka', 'product_version' => 'v.20260315', 'environment' => 'penggunaan di posko, kondisi panas',
                'reproduce_steps' => 'tinggal diulangi saja', 'root_cause' => null, 'repair_action' => null, 'is_rework' => 0, 'attachment_path' => '1773533739_d56affc8390e9e88a980.jpeg',
                'expected_result' => 'sesuai yang diharapkan', 'reported_by' => 'Maneng', 'status' => 'OPEN', 'fixed_by' => null,
                'closed_at' => null, 'created_at' => '2026-03-15 07:15:39', 'updated_at' => '2026-03-15 07:15:39'
            ],
            [
                'id' => 28, 'project_id' => 27, 'title' => 'Kapasitor Meledak', 'severity' => 'Major',
                'serial_number_id' => 17, 'sn_code_snapshot' => null, 'reporter_type' => 'produk',
                'description' => 'kjkjkjkjkjkjkjkj', 'product_version' => 'versi optim 20260221', 'environment' => 'okokokokokoko',
                'reproduce_steps' => null, 'root_cause' => null, 'repair_action' => '', 'is_rework' => 0, 'attachment_path' => '1773584494_47b05dc31ac7787bcc31.mp4',
                'expected_result' => null, 'reported_by' => 'manufacture', 'status' => 'CLOSED', 'fixed_by' => null,
                'closed_at' => null, 'created_at' => '2026-03-15 21:21:34', 'updated_at' => '2026-03-30 11:27:08'
            ],
            [
                'id' => 29, 'project_id' => 27, 'title' => 'Kapasitor Meledak', 'severity' => 'Major',
                'serial_number_id' => 10527, 'sn_code_snapshot' => null, 'reporter_type' => 'produk',
                'description' => 'detailllllllll', 'product_version' => 'v.20260315', 'environment' => 'asdfadfasdfadfadfad',
                'reproduce_steps' => null, 'root_cause' => null, 'repair_action' => '111111', 'is_rework' => 0, 'attachment_path' => null,
                'expected_result' => null, 'reported_by' => 'program', 'status' => 'CLOSED', 'fixed_by' => null,
                'closed_at' => null, 'created_at' => '2026-03-15 23:02:24', 'updated_at' => '2026-03-16 10:18:23'
            ],
            [
                'id' => 30, 'project_id' => 176, 'title' => 'aaaaaaaaaaaaaaaaa', 'severity' => 'Minor',
                'serial_number_id' => 10300, 'sn_code_snapshot' => null, 'reporter_type' => 'sub',
                'description' => 'aaaaaaaaaaaaaaa', 'product_version' => 'aaaaaaaaaaaaaa', 'environment' => 'aaaaaaaaaaaaa',
                'reproduce_steps' => null, 'root_cause' => null, 'repair_action' => 'bbbbbbbbbbbbb', 'is_rework' => 0, 'attachment_path' => null,
                'expected_result' => null, 'reported_by' => 'Maneng', 'status' => 'CLOSED', 'fixed_by' => null,
                'closed_at' => null, 'created_at' => '2026-03-16 10:21:22', 'updated_at' => '2026-03-16 10:21:45'
            ],
            [
                'id' => 31, 'project_id' => 177, 'title' => 'ccccccccccccccccc', 'severity' => 'Minor',
                'serial_number_id' => 10302, 'sn_code_snapshot' => null, 'reporter_type' => 'sub',
                'description' => 'cccccccccccccc', 'product_version' => 'cccccccccccccc', 'environment' => 'ccccccccccccc',
                'reproduce_steps' => null, 'root_cause' => null, 'repair_action' => 'dddddddddddddddd', 'is_rework' => 0, 'attachment_path' => null,
                'expected_result' => null, 'reported_by' => 'manufacture', 'status' => 'CLOSED', 'fixed_by' => null,
                'closed_at' => null, 'created_at' => '2026-03-16 10:30:51', 'updated_at' => '2026-03-16 10:31:33'
            ],
            [
                'id' => 32, 'project_id' => 177, 'title' => 'eeeeeeeeeeeeeeee', 'severity' => 'Major',
                'serial_number_id' => 10303, 'sn_code_snapshot' => null, 'reporter_type' => 'sub',
                'description' => 'eeeeeeeeeeee', 'product_version' => 'eeeeeeeeee', 'environment' => 'eeeeeeeeeeeee',
                'reproduce_steps' => null, 'root_cause' => null, 'repair_action' => 'fffffffffffffffff', 'is_rework' => 0, 'attachment_path' => null,
                'expected_result' => null, 'reported_by' => 'program', 'status' => 'CLOSED', 'fixed_by' => null,
                'closed_at' => null, 'created_at' => '2026-03-16 10:37:26', 'updated_at' => '2026-03-16 10:37:45'
            ],
            [
                'id' => 33, 'project_id' => 176, 'title' => 'aaaaaaaaaaaaaaaaa', 'severity' => 'Major',
                'serial_number_id' => 10300, 'sn_code_snapshot' => null, 'reporter_type' => 'sub',
                'description' => 'bcbcbcbc', 'product_version' => 'bbbbbbbbbbbbbbbbbbbbbbbb', 'environment' => 'etetwt',
                'reproduce_steps' => null, 'root_cause' => null, 'repair_action' => 'fffffffffffffffffff', 'is_rework' => 0, 'attachment_path' => '1774598453_064f2e72d0348089b19b.png',
                'expected_result' => null, 'reported_by' => 'Admin', 'status' => 'CLOSED', 'fixed_by' => null,
                'closed_at' => null, 'created_at' => '2026-03-27 15:00:53', 'updated_at' => '2026-03-27 16:15:34'
            ],
            [
                'id' => 34, 'project_id' => 177, 'title' => 'Kapasitor Meledak', 'severity' => 'Major',
                'serial_number_id' => 10302, 'sn_code_snapshot' => null, 'reporter_type' => 'sub',
                'description' => 'sgtgrgtrgr', 'product_version' => '111', 'environment' => 'eeeeeeeeeeeee',
                'reproduce_steps' => null, 'root_cause' => null, 'repair_action' => 'ewygyye', 'is_rework' => 0, 'attachment_path' => null,
                'expected_result' => null, 'reported_by' => 'Admin', 'status' => 'CLOSED', 'fixed_by' => null,
                'closed_at' => null, 'created_at' => '2026-03-27 15:05:12', 'updated_at' => '2026-03-27 16:11:00'
            ],
            [
                'id' => 35, 'project_id' => 176, 'title' => 'Kapasitor Meledak', 'severity' => 'Major',
                'serial_number_id' => 10301, 'sn_code_snapshot' => null, 'reporter_type' => 'sub',
                'description' => 'WEAFEAFrfrf', 'product_version' => 'adAD', 'environment' => 'AFWFEAF',
                'reproduce_steps' => null, 'root_cause' => null, 'repair_action' => 'WRQFERFrfrwqff', 'is_rework' => 0, 'attachment_path' => null,
                'expected_result' => null, 'reported_by' => 'Fioni Agriyani', 'status' => 'CLOSED', 'fixed_by' => 'Admin', // mapped lowercase admin to Admin
                'closed_at' => '2026-04-09 12:35:12', 'created_at' => '2026-03-27 16:03:02', 'updated_at' => '2026-03-27 16:04:00'
            ],
            [
                'id' => 36, 'project_id' => 189, 'title' => 'Solderan Retak (Cold Solder)', 'severity' => 'Major',
                'serial_number_id' => 10541, 'sn_code_snapshot' => null, 'reporter_type' => 'sub',
                'description' => 'Jalur solderan pada PCB mengalami keretakan.', 'product_version' => 'V1.0.0', 'environment' => 'Baik',
                'reproduce_steps' => null, 'root_cause' => 'Panas berlebih, kelembapan (korosi) dan penggunaan daya yang terlalu tinggi..', 'repair_action' => 'Resoldering pada bagian yang mengalami keretakan.', 'is_rework' => 1, 'attachment_path' => null,
                'expected_result' => null, 'reported_by' => 'Admin', 'status' => 'CLOSED', 'fixed_by' => 'Fioni Agriyani',
                'closed_at' => '2026-04-10 16:09:00', 'created_at' => '2026-03-30 11:15:46', 'updated_at' => '2026-04-09 12:38:46'
            ],
            [
                'id' => 37, 'project_id' => 27, 'title' => 'judul trouble', 'severity' => 'Major',
                'serial_number_id' => 17, 'sn_code_snapshot' => null, 'reporter_type' => 'produk',
                'description' => 'desc 20260409', 'product_version' => 'class default', 'environment' => 'jkjkjk',
                'reproduce_steps' => null, 'root_cause' => null, 'repair_action' => 'berbagai hal dilakukan ', 'is_rework' => 0, 'attachment_path' => null,
                'expected_result' => null, 'reported_by' => 'Admin', 'status' => 'CLOSED', 'fixed_by' => null,
                'closed_at' => '2026-04-09 12:41:34', 'created_at' => '2026-04-09 12:40:25', 'updated_at' => '2026-04-09 12:41:34'
            ],
            [
                'id' => 38, 'project_id' => 27, 'title' => 'judul trouble 20290409', 'severity' => 'Major',
                'serial_number_id' => 4, 'sn_code_snapshot' => null, 'reporter_type' => 'produk',
                'description' => 'klklklkalsdkfalsdk', 'product_version' => 'versi optim 20260221', 'environment' => 'penggunaan di posko, kondisi panas',
                'reproduce_steps' => null, 'root_cause' => null, 'repair_action' => 'ini adalah perbaikan', 'is_rework' => 0, 'attachment_path' => null,
                'expected_result' => null, 'reported_by' => 'Admin', 'status' => 'CLOSED', 'fixed_by' => null,
                'closed_at' => '2026-04-09 13:09:23', 'created_at' => '2026-04-09 12:48:58', 'updated_at' => '2026-04-09 13:09:23'
            ],
            [
                'id' => 39, 'project_id' => 27, 'title' => 'Kapasitor Meledak', 'severity' => 'Major',
                'serial_number_id' => 4, 'sn_code_snapshot' => null, 'reporter_type' => 'produk',
                'description' => 'desksksksksksksk', 'product_version' => 'versi optim 20260221', 'environment' => 'penggunaan di posko, kondisi panas',
                'reproduce_steps' => null, 'root_cause' => null, 'repair_action' => 'tindakakanana', 'is_rework' => 0, 'attachment_path' => null,
                'expected_result' => null, 'reported_by' => 'Admin', 'status' => 'CLOSED', 'fixed_by' => null,
                'closed_at' => '2026-04-09 13:20:48', 'created_at' => '2026-04-09 13:20:26', 'updated_at' => '2026-04-09 13:20:48'
            ],
            [
                'id' => 40, 'project_id' => 27, 'title' => 'Kapasitor Meledak 02', 'severity' => 'Major',
                'serial_number_id' => 4, 'sn_code_snapshot' => null, 'reporter_type' => 'produk',
                'description' => 'desedsedsedsdseesds', 'product_version' => 'versi optim 20260221', 'environment' => null,
                'reproduce_steps' => null, 'root_cause' => null, 'repair_action' => null, 'is_rework' => 0, 'attachment_path' => null,
                'expected_result' => null, 'reported_by' => 'Admin', 'status' => 'OPEN', 'fixed_by' => null,
                'closed_at' => null, 'created_at' => '2026-04-09 14:04:35', 'updated_at' => '2026-04-09 14:04:35'
            ],
            [
                'id' => 41, 'project_id' => 27, 'title' => 'Kapasitor Meledak 03', 'severity' => 'Major',
                'serial_number_id' => 4, 'sn_code_snapshot' => null, 'reporter_type' => 'produk',
                'description' => 'desdeededdeedededed', 'product_version' => 'versi optim 20260221', 'environment' => 'enviviiviviivvii',
                'reproduce_steps' => null, 'root_cause' => null, 'repair_action' => 'perbaikaaaaaaaaaa', 'is_rework' => 0, 'attachment_path' => null,
                'expected_result' => null, 'reported_by' => '1', 'status' => 'CLOSED', 'fixed_by' => '1',
                'closed_at' => '2026-04-09 14:51:45', 'created_at' => '2026-04-09 14:50:30', 'updated_at' => '2026-04-09 14:51:45'
            ],
            [
                'id' => 42, 'project_id' => 27, 'title' => 'Kapasitor Meledak 04', 'severity' => 'Major',
                'serial_number_id' => 4, 'sn_code_snapshot' => null, 'reporter_type' => 'produk',
                'description' => 'dededededesdedsdsedsedsedes', 'product_version' => 'versi optim 20260221', 'environment' => 'penggunaan di posko, kondisi panas',
                'reproduce_steps' => null, 'root_cause' => null, 'repair_action' => 'repairiririririri', 'is_rework' => 0, 'attachment_path' => null,
                'expected_result' => null, 'reported_by' => 'Admin', 'status' => 'CLOSED', 'fixed_by' => 'Admin',
                'closed_at' => '2026-04-09 15:05:26', 'created_at' => '2026-04-09 15:01:18', 'updated_at' => '2026-04-09 15:05:26'
            ],
            [
                'id' => 43, 'project_id' => 27, 'title' => 'Kapasitor Meledak 05', 'severity' => 'Major',
                'serial_number_id' => 4, 'sn_code_snapshot' => null, 'reporter_type' => 'produk',
                'description' => 'dedededdedeedesssdsedse', 'product_version' => 'versi optim 20260221', 'environment' => 'penggunaan di posko, kondisi panas',
                'reproduce_steps' => null, 'root_cause' => null, 'repair_action' => 'repepepepepepepe', 'is_rework' => 0, 'attachment_path' => null,
                'expected_result' => null, 'reported_by' => 'Admin', 'status' => 'CLOSED', 'fixed_by' => 'Admin',
                'closed_at' => '2026-04-09 15:17:32', 'created_at' => '2026-04-09 15:16:17', 'updated_at' => '2026-04-09 15:17:32'
            ],
            [
                'id' => 44, 'project_id' => 27, 'title' => 'Kapasitor Meledak 06', 'severity' => 'Major',
                'serial_number_id' => 4, 'sn_code_snapshot' => null, 'reporter_type' => 'produk',
                'description' => 'dedededededededededesss', 'product_version' => 'versi optim 20260221', 'environment' => 'penggunaan di posko, kondisi panas',
                'reproduce_steps' => 'hohohohohohoho', 'root_cause' => null, 'repair_action' => null, 'is_rework' => 0, 'attachment_path' => null,
                'expected_result' => 'exexexexxexex', 'reported_by' => 'Admin', 'status' => 'OPEN', 'fixed_by' => null,
                'closed_at' => null, 'created_at' => '2026-04-10 07:03:54', 'updated_at' => '2026-04-10 07:03:54'
            ],
            [
                'id' => 45, 'project_id' => 27, 'title' => 'Kapasitor Meledak 07', 'severity' => 'Major',
                'serial_number_id' => 4, 'sn_code_snapshot' => 'SN_UNIT_TACA#OPSHYB_2026-001', 'reporter_type' => 'produk',
                'description' => 'dedededsdsedsedsdsde', 'product_version' => 'versi optim 20260221', 'environment' => 'penggunaan di posko, kondisi panas',
                'reproduce_steps' => null, 'root_cause' => null, 'repair_action' => 'perbaiaiaiaiaiaiaiakan', 'is_rework' => 0, 'attachment_path' => null,
                'expected_result' => null, 'reported_by' => 'Admin', 'status' => 'CLOSED', 'fixed_by' => 'Admin',
                'closed_at' => '2026-04-10 07:18:41', 'created_at' => '2026-04-10 07:15:08', 'updated_at' => '2026-04-10 07:18:41'
            ],
            [
                'id' => 46, 'project_id' => 27, 'title' => 'Kapasitor Meledak 08', 'severity' => 'Major',
                'serial_number_id' => 4, 'sn_code_snapshot' => 'SN_UNIT_TACA#OPSHYB_2026-001', 'reporter_type' => 'produk',
                'description' => 'dededededededes', 'product_version' => 'versi optim 20260221', 'environment' => 'penggunaan di posko, kondisi panas',
                'reproduce_steps' => null, 'root_cause' => null, 'repair_action' => 'tctctctctctctctctctindakan', 'is_rework' => 0, 'attachment_path' => null,
                'expected_result' => null, 'reported_by' => 'Admin', 'status' => 'CLOSED', 'fixed_by' => 'Admin',
                'closed_at' => '2026-04-10 07:31:55', 'created_at' => '2026-04-10 07:31:16', 'updated_at' => '2026-04-10 07:31:55'
            ],
            [
                'id' => 47, 'project_id' => 27, 'title' => 'Kapasitor Meledak 09', 'severity' => 'Major',
                'serial_number_id' => 4, 'sn_code_snapshot' => 'SN_UNIT_TACA#OPSHYB_2026-001', 'reporter_type' => 'produk',
                'description' => 'dedededededdeesss', 'product_version' => 'versi optim 20260221', 'environment' => 'penggunaan di posko, kondisi panas',
                'reproduce_steps' => null, 'root_cause' => null, 'repair_action' => 'tintitnitntintitnitn', 'is_rework' => 0, 'attachment_path' => null,
                'expected_result' => null, 'reported_by' => 'Admin', 'status' => 'CLOSED', 'fixed_by' => 'Admin',
                'closed_at' => '2026-04-10 07:46:45', 'created_at' => '2026-04-10 07:45:31', 'updated_at' => '2026-04-10 07:46:45'
            ],
            [
                'id' => 48, 'project_id' => 27, 'title' => 'Kapasitor Meledak 010', 'severity' => 'Major',
                'serial_number_id' => 4, 'sn_code_snapshot' => 'SN_UNIT_TACA#OPSHYB_2026-001', 'reporter_type' => 'produk',
                'description' => 'dedsedsedsdsdsed', 'product_version' => 'versi optim 20260221', 'environment' => 'penggunaan di posko, kondisi panas',
                'reproduce_steps' => null, 'root_cause' => null, 'repair_action' => 'prprprprprprprprp', 'is_rework' => 0, 'attachment_path' => null,
                'expected_result' => null, 'reported_by' => 'Admin', 'status' => 'CLOSED', 'fixed_by' => 'Admin',
                'closed_at' => '2026-04-10 07:55:24', 'created_at' => '2026-04-10 07:55:04', 'updated_at' => '2026-04-10 07:55:24'
            ],
            [
                'id' => 49, 'project_id' => 27, 'title' => 'Kapasitor Meledak 011', 'severity' => 'Major',
                'serial_number_id' => 4, 'sn_code_snapshot' => 'SN_UNIT_TACA#OPSHYB_2026-001', 'reporter_type' => 'produk',
                'description' => 'ini edit deskripsi', 'product_version' => 'vevevevevev', 'environment' => 'penggunaan di posko, kondisi panas',
                'reproduce_steps' => 'hohohohohohoho', 'root_cause' => 'akakrkrkrkrkrkkrrkk', 'repair_action' => 'tintitnitnitntinti', 'is_rework' => 0, 'attachment_path' => null,
                'expected_result' => 'edxexexexexexexexexexe', 'reported_by' => 'Admin', 'status' => 'CLOSED', 'fixed_by' => 'Admin',
                'closed_at' => '2026-04-10 09:45:27', 'created_at' => '2026-04-10 08:06:22', 'updated_at' => '2026-04-10 08:06:22'
            ],
            [
                'id' => 50, 'project_id' => 27, 'title' => 'Kapasitor Meledak 012', 'severity' => 'Major',
                'serial_number_id' => 4, 'sn_code_snapshot' => 'SN_UNIT_TACA#OPSHYB_2026-001', 'reporter_type' => 'produk',
                'description' => 'desdsedsefsefsef', 'product_version' => 'versi optim 20260221', 'environment' => 'penggunaan di posko, kondisi panas',
                'reproduce_steps' => 'ohohohohohohsodfhsod', 'root_cause' => 'rorororororoor', 'repair_action' => 'arerererererer', 'is_rework' => 0, 'attachment_path' => '1775788069_e4bcadc95b8a1ad558fa.jpeg',
                'expected_result' => 'exsexesxerxsrda', 'reported_by' => 'Admin', 'status' => 'CLOSED', 'fixed_by' => 'Admin',
                'closed_at' => '2026-04-10 09:37:14', 'created_at' => '2026-04-10 09:27:49', 'updated_at' => '2026-04-10 09:27:49'
            ],
            [
                'id' => 51, 'project_id' => 27, 'title' => 'Kapasitor Meledak 013', 'severity' => 'Major',
                'serial_number_id' => 4, 'sn_code_snapshot' => 'SN_UNIT_TACA#OPSHYB_2026-001', 'reporter_type' => 'produk',
                'description' => 'adsfadsfasdfasdfasdf', 'product_version' => 'vadsasdvadsv', 'environment' => 'asdfadfadfadsfa',
                'reproduce_steps' => 'asdfadfadfadfa', 'root_cause' => 'contoh', 'repair_action' => 'contoh', 'is_rework' => 1, 'attachment_path' => '1775789300_be186a679ce6be5c9709.jpeg',
                'expected_result' => 'adfasdfadfadfdf', 'reported_by' => 'Admin', 'status' => 'CLOSED', 'fixed_by' => 'Fioni Agriyani',
                'closed_at' => '2026-04-10 11:21:41', 'created_at' => '2026-04-10 09:48:20', 'updated_at' => '2026-04-10 09:48:20'
            ],
            [
                'id' => 52, 'project_id' => 192, 'title' => 'contoh', 'severity' => 'Major',
                'serial_number_id' => 10559, 'sn_code_snapshot' => 'sn-contoh-001.1', 'reporter_type' => 'sub',
                'description' => 'contoh', 'product_version' => 'V1.0.0', 'environment' => 'contoh',
                'reproduce_steps' => 'contoh', 'root_cause' => 'contoh', 'repair_action' => 'contoh', 'is_rework' => 0, 'attachment_path' => null,
                'expected_result' => 'contoh', 'reported_by' => 'Fioni Agriyani', 'status' => 'CLOSED', 'fixed_by' => 'Fioni Agriyani',
                'closed_at' => '2026-04-10 11:21:03', 'created_at' => '2026-04-10 11:20:09', 'updated_at' => '2026-04-10 11:20:09'
            ],
            [
                'id' => 53, 'project_id' => 27, 'title' => 'Kapasitor Meledak 014', 'severity' => 'Critical',
                'serial_number_id' => 4, 'sn_code_snapshot' => 'SN_UNIT_TACA#OPSHYB_2026-001', 'reporter_type' => 'produk',
                'description' => 'desdsedsedseds', 'product_version' => 'versi optim 20260221', 'environment' => 'penggunaan di posko, kondisi panas',
                'reproduce_steps' => 'hohohohohohoho', 'root_cause' => 'rororoaorofaroatoar', 'repair_action' => 'reaoekaoekaokeraoekaok', 'is_rework' => 0, 'attachment_path' => '1775799776_7483b5da4974c42044f8.jpeg',
                'expected_result' => 'saeroaroaseoaseorao', 'reported_by' => 'Admin', 'status' => 'CLOSED', 'fixed_by' => 'Admin',
                'closed_at' => '2026-04-10 12:43:18', 'created_at' => '2026-04-10 12:42:56', 'updated_at' => '2026-04-10 12:42:56'
            ],
            [
                'id' => 54, 'project_id' => 27, 'title' => 'Kapasitor Meledak 015-edit', 'severity' => 'Minor',
                'serial_number_id' => 4, 'sn_code_snapshot' => 'SN_UNIT_TACA#OPSHYB_2026-001', 'reporter_type' => 'produk',
                'description' => 'asdfadfasdfadfaaf', 'product_version' => 'versi optim 20260221', 'environment' => 'enviviiviviivvii',
                'reproduce_steps' => 'adfadflkaodfkadofkaok', 'root_cause' => 'oidfoiaosdifaodifaoidfoaio', 'repair_action' => 'piarpipipipipipipoiad', 'is_rework' => 1, 'attachment_path' => '1775800040_e7b3d0fbdc9ab591802c.jpeg',
                'expected_result' => 'okokokokokoaskdfoakfoa', 'reported_by' => 'Admin', 'status' => 'CLOSED', 'fixed_by' => 'Admin',
                'closed_at' => '2026-04-10 12:48:05', 'created_at' => '2026-04-10 12:47:20', 'updated_at' => '2026-04-10 12:47:20'
            ],
            [
                'id' => 55, 'project_id' => 192, 'title' => 'contoh', 'severity' => 'Major',
                'serial_number_id' => 10560, 'sn_code_snapshot' => 'sn-contoh-001.2', 'reporter_type' => 'sub',
                'description' => 'contoh', 'product_version' => 'V1.0.0', 'environment' => 'contoh',
                'reproduce_steps' => 'contoh', 'root_cause' => 'contoh', 'repair_action' => 'contoh', 'is_rework' => 1, 'attachment_path' => null,
                'expected_result' => 'contoh', 'reported_by' => 'Fioni Agriyani', 'status' => 'CLOSED', 'fixed_by' => 'Fioni Agriyani',
                'closed_at' => '2026-04-10 13:41:49', 'created_at' => '2026-04-10 13:40:54', 'updated_at' => '2026-04-10 13:40:54'
            ],
            [
                'id' => 56, 'project_id' => 192, 'title' => 'contoh', 'severity' => 'Major',
                'serial_number_id' => 10561, 'sn_code_snapshot' => 'sn-contoh-001.3', 'reporter_type' => 'sub',
                'description' => 'contoh', 'product_version' => 'V1.0.0', 'environment' => 'contoh',
                'reproduce_steps' => 'contoh', 'root_cause' => 'contoh', 'repair_action' => 'contoh', 'is_rework' => 0, 'attachment_path' => null,
                'expected_result' => 'contoh', 'reported_by' => 'Fioni Agriyani', 'status' => 'CLOSED', 'fixed_by' => 'Fioni Agriyani',
                'closed_at' => '2026-04-10 13:42:02', 'created_at' => '2026-04-10 13:41:15', 'updated_at' => '2026-04-10 13:41:15'
            ],
        ];

        foreach ($bugsData as $bug) {
            // Find reporter user ID
            $reporterName = strtolower($bug['reported_by'] ?? 'admin');
            $reporterId = isset($users[$reporterName]) ? $users[$reporterName]->id : $users['admin']->id;

            // Find fixer user ID
            $fixerId = null;
            if ($bug['fixed_by']) {
                $fixerName = strtolower($bug['fixed_by']);
                $fixerId = isset($users[$fixerName]) ? $users[$fixerName]->id : $users['admin']->id;
            }

            Bug::create([
                'id' => $bug['id'],
                'project_id' => $bug['project_id'],
                'title' => $bug['title'],
                'severity' => $bug['severity'],
                'serial_number_id' => $bug['serial_number_id'],
                'sn_code_snapshot' => $bug['sn_code_snapshot'],
                'reporter_type' => $bug['reporter_type'],
                'device_id' => null, // all device ids are null in the original dataset
                'description' => $bug['description'],
                'product_version' => $bug['product_version'],
                'environment' => $bug['environment'],
                'reproduce_steps' => $bug['reproduce_steps'],
                'root_cause' => $bug['root_cause'],
                'repair_action' => $bug['repair_action'],
                'is_rework' => $bug['is_rework'],
                'attachment_path' => $bug['attachment_path'],
                'expected_result' => $bug['expected_result'],
                'reported_by' => $reporterId,
                'status' => $bug['status'],
                'fixed_by' => $fixerId,
                'closed_at' => $bug['closed_at'] ? Carbon::parse($bug['closed_at']) : null,
                'created_at' => Carbon::parse($bug['created_at']),
                'updated_at' => Carbon::parse($bug['updated_at']),
                
                // Historical rows: set AI attributes to null/false
                'sentiment_label' => null,
                'sentiment_score' => null,
                'is_spam' => false,
                'spam_reason' => null,
                'severity_recommended' => null,
                'severity_recommendation_reason' => null,
                'damage_category' => null,
            ]);
        }
    }
}
