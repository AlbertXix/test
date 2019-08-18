import java.util.Random;

public class WxRedPackage {

    private static int remainSize;
    
    private static double remainMoney;

    public static void main(String args[]) {
        WxRedPackage.init();
        int loopSize = remainSize;
        double totalMoney = 0;
        for (int i = 0; i < loopSize; i++) {
            double money = WxRedPackage.getRandomMoney();
            totalMoney += money;
            System.out.printf("wx red packae: %f\n", money);
        }

        System.out.println("total money: " + totalMoney);
    }

    public static void init() {
        remainSize  = 30;
        remainMoney = 500;
    }

    public static double getRandomMoney() {
        if (remainSize == 1) {
            remainSize--;
            return (double) Math.round(remainMoney * 100) / 100;
        }

        Random r     = new Random();
        double min   = 0.01; //
        double max   = remainMoney / remainSize * 2;
        double money = r.nextDouble() * max;
        money = money <= min ? 0.01: money;
        money = Math.floor(money * 100) / 100;
        remainSize--;
        remainMoney -= money;

        return money;
    }

}

